<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 06/09/2018
 * Time: 11:16
 */

namespace App\Controller\Api;

use App\Entity\Order;
use App\Entity\User;
use App\Form\EditOrderType;
use App\Form\NewOrderType;
use App\Repository\OrderRepository;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/api/")
 * Class OrderController
 * @package App\Controller\Api
 */
class OrderController extends Controller
{
    /**
     * @Route("orders/new", name="new_order")
     * @SWG\Post(
     *        tags={"orders"},
     *        operationId="createOrder",
     *        summary="Create new order entry"
     * )
     * @SWG\Response(
     *      response="400",
     *      description="Validation Error",
     *      @SWG\Schema(
     *            type="array",
     *          	@SWG\Items(
     *                type="object",
     *              	@SWG\Property(property="orderName", type="string"),
     *              	@SWG\Property(property="cpu", type="string"),
     *              	@SWG\Property(property="ram", type="integer"),
     *              	@SWG\Property(property="hdd", type="integer"),
     *              	@SWG\Property(property="screen", type="integer"),
     *              	@SWG\Property(property="comment", type="string"),
     *            ),
     *      )
     * )
     */
    public function newOrder(Request $request, \Swift_Mailer $mailer) // UserInterface $user
    {
        $order = new Order();
        $user = $this->getUser();
        $priceController = new PriceController();

        $form = $this->createForm(NewOrderType::class, $order);
        $form->submit(json_decode($request->getContent(), true));
        $this->get('serializer')->deserialize($request->getContent(), Order::class, 'json', ['object_to_populate' => $order]);

        $order->setUser($user);
        $order->setOrderName($user->getUsername());

        $order->setPrice($priceController->calculate(
            $order->getCpu(),
            $order->getRam(),
            $order->getHdd(),
            $order->getScreen()
        ));

        if ($form->isSubmitted()) {
            $time = new \DateTime();
            $order->setCreatedAt($time);

            $order = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($order);
            $entityManager->flush();

            $message = (new \Swift_Message('Order accepted ' . ucfirst($user->getUsername()) . '!'))
                ->setFrom('petermailer777@gmail.com')
                ->setTo($user->getEmail())
//                ->setTo("ochenx@gmail.com")
                ->setBody(
                    'Hello ' . ucfirst($user->getUsername()) . '! ' .
                    'Your order has been accepted, you will be notified when the order will be sent.
                Your order:
                     cpu: ' . $order->getCpu() .
                    ' ram: ' . $order->getRam() .
                    ' hdd: ' . $order->getHdd() .
                    ' screen:' . $order->getScreen() .
                    ' price:' . $order->getPrice()
                );
            $mailer->send($message);

            return new JsonResponse([
                'orderName' => $order->getOrderName(),
                'cpu' => $order->getCpu(),
                'ram' => $order->getRam(),
                'hdd' => $order->getHdd(),
                'screen' => $order->getScreen(),
            ]);
        }
        return new JsonResponse([
            'orderName' => "unknown",
            'cpu' => "unknown",
            'ram' => "unknown",
            'hdd' => "unknown",
            'screen' => "unknown"
        ]);
    }

    /**
     * @Route("api/orders", name="show_orders")
     * @SWG\Get(
     *        tags={"orders"},
     *        operationId="showAllOrders",
     *        summary="Show all orders"
     * )
     * @SWG\Response(
     *      response="400",
     *      description="Validation Error",
     *      @SWG\Schema(
     *            type="array",
     *          	@SWG\Items(
     *                type="object",
     *              	@SWG\Property(property="orderName", type="string"),
     *              	@SWG\Property(property="cpu", type="string"),
     *              	@SWG\Property(property="ram", type="integer"),
     *              	@SWG\Property(property="hdd", type="integer"),
     *              	@SWG\Property(property="screen", type="integer"),
     *              	@SWG\Property(property="comment", type="string"),
     *            ),
     *      )
     * )
     */
    public function index(Request $request, OrderRepository $orderRepository)
    {
        $user = $this->getUser();
        $currency = "$";
        $totalPrice = 0;

        if(!empty($user))
        {
            $orders = $this->getDoctrine()->getRepository(Order::class)->findBy([
                'user' => $user
            ]);
            foreach ($orders as $order) {
                $totalPrice += $order->getPrice();
            }
        } else {
            $orders = null;
        }

        $userId = $user->getId();
        $qb = $orderRepository->createQueryBuilder('o')
            ->setParameter('q', '' . $userId . '')
            ->andWhere('o.user = :q');
        $query = $qb->getQuery(); //    ->getResult();

        $paginator = $this->get('knp_paginator');
        $_orders = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        $orders = [];
        foreach ($_orders as $order) {
            $orders[] = $order;
        }

        $context = new SerializationContext();
        $context
            ->setSerializeNull(true)
            ->setGroups(['api']);

        return new JsonResponse([
                'user' => $user->getUsername(),
                'orders' => json_decode($this->get('jms_serializer')->serialize($orders,'json', $context)),
                'totalPrice' => $totalPrice,
                'currency' => $currency
        ]);
    }

    /**
     * @Route("orders/edit/{id}", name="edit_order")
     * @SWG\Put(
     *        tags={"orders"},
     *        operationId="editOrder",
     *        summary="Edit order"
     * )
     * @SWG\Response(
     *      response="400",
     *      description="Validation Error",
     *      @SWG\Schema(
     *            type="array",
     *          	@SWG\Items(
     *                type="object",
     *              	@SWG\Property(property="orderName", type="string"),
     *              	@SWG\Property(property="cpu", type="string"),
     *              	@SWG\Property(property="ram", type="integer"),
     *              	@SWG\Property(property="hdd", type="integer"),
     *              	@SWG\Property(property="screen", type="integer"),
     *              	@SWG\Property(property="comment", type="string"),
     *            ),
     *      )
     * )
     */
    public function edit(Request $request, $id)
    {
        $user = $this->getUser();
        $priceController = new PriceController();
        $order = $this->getDoctrine()->getRepository(Order::class)->find($id);

        if ($order)
        {
            $form = $this->createForm(EditOrderType::class, $order);

            $context = new SerializationContext();
            $context
                ->setSerializeNull(true)
                ->setGroups(['api']);       // $jsonOrder = json_decode($this->get('jms_serializer')->serialize($order,'json', $context));

            $this->get('serializer')->deserialize($request->getContent(), Order::class, 'json', ['object_to_populate' => $order]);

            $formData = $form->setData($order);
            $form->submit($formData);

            $order->setPrice($priceController->calculate(
                $order->getCpu(),
                $order->getRam(),
                $order->getHdd(),
                $order->getScreen()
            ));

            if ($form->isSubmitted()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($order);
                $entityManager->flush();

                $context = new SerializationContext();
                $context
                    ->setSerializeNull(true)
                    ->setGroups(['api']);

                return new JsonResponse([
                    'status' => 'ok',
                    'order' => json_decode($this->get('jms_serializer')->serialize($order,'json', $context)),
                ]);
            }
        }
        return new JsonResponse(['status' => 'nok']);
    }

    /**
     * @Route("orders/{id}", name="show_order")
     * @SWG\Get(
     *        tags={"orders"},
     *        operationId="showOrder",
     *        summary="Show order"
     * )
     * @SWG\Response(
     *      response="400",
     *      description="Validation Error",
     *      @SWG\Schema(
     *            type="array",
     *          	@SWG\Items(
     *                type="object",
     *              	@SWG\Property(property="orderName", type="string"),
     *              	@SWG\Property(property="cpu", type="string"),
     *              	@SWG\Property(property="ram", type="integer"),
     *              	@SWG\Property(property="hdd", type="integer"),
     *              	@SWG\Property(property="screen", type="integer"),
     *              	@SWG\Property(property="comment", type="string"),
     *            ),
     *      )
     * )
     */
    public function show($id)
    {
        $user = $this->getUser();

        $order = $this->getDoctrine()->getRepository(Order::class)->findOneBy([
            'id' => $id,
            'orderName' => $user->getUsername()
        ]);
        $currency = "$";
        $orderPrice = $order->getPrice();

        $context = new SerializationContext();
        $context
            ->setSerializeNull(true)
            ->setGroups(['api']);

        return new JsonResponse([
            'order' => json_decode($this->get('jms_serializer')->serialize($order,'json', $context)),
            'orderPrice' => $orderPrice,
            'currency' => $currency
        ]);
    }
}
