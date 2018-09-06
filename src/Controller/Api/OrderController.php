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
use App\Form\NewOrderType;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;

class OrderController extends Controller
{
    /**
     * @Route("api/order/new", name="new_order")
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
     *              	@SWG\Property(property="name", type="string"),
     *              	@SWG\Property(property="category", type="string"),
     *              	@SWG\Property(property="cpu", type="string"),
     *              	@SWG\Property(property="ram", type="integer"),
     *              	@SWG\Property(property="hdd", type="integer"),
     *              	@SWG\Property(property="screen", type="integer"),
     *            ),
     *      )
     * )
     */
    public function newOrder(Request $request, \Swift_Mailer $mailer)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'username' => 'yoman'
        ]);
        $order = new Order();
        $priceController = new PriceController();

        $form = $this->createForm(NewOrderType::class, $order);
        $form->get('name')->setData($user->getUsername());
        $form->handleRequest($request);

        $time = new \DateTime();
        $order->setDate($time);

        $order->setUser($user);

        $order->setPrice($priceController->calculate(
            $order->getCpu(),
            $order->getRam(),
            $order->getHdd(),
            $order->getScreen()
        ));

        $order->setName("hey");
        $order->setCpu("i5");
        $order->setRam(32);
        $order->setHdd(512);
        $order->setScreen(13);
        $request = $order;

        if ($form->isSubmitted() && $form->isValid()) {
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

            $response = new JsonResponse([
                'name' => $order->getName(),
                'cpu' => $order->getCpu(),
                'ram' => $order->getRam(),
                'hdd' => $order->getHdd(),
                'screen' => $order->getScreen(),
            ]);

            return $response;
        }

//        return $this->render('orders/new.html.twig', array(
//            'form' => $form->createView()
//        ));

        $response = new JsonResponse([
            'name' => $order->getName(),
            'cpu' => $order->getCpu(),
            'ram' => $order->getRam(),
            'hdd' => $order->getHdd(),
            'screen' => $order->getScreen(),
        ]);

        return $response;
    }

    /**
     * @Route("/api/orders", name="index")
     */
    public function index(Request $request, OrderRepository $orderRepository)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'id' => 1
        ]);

        if(!empty($user))
        {
            $orders = $this->getDoctrine()->getRepository(Order::class)->findBy([
                'user' => $user
            ]);
        } else {
            $orders = null;
        }
        $locale = $this->getUser()->getLocale();

        $currency = "$";
        $totalPrice = 0;
        foreach ($orders as $order) {
            $totalPrice += $order->getPrice();
            if ($locale == "pl_PL" || $locale == "pl") {
                //$order->setPrice($this->convertCurrency($order->getPrice(), 'USD', 'PLN')); // ZA DUŻO REQUESTÓW FREE
                $order->setPrice($order->getPrice() * 3.8);
            }
        }

        if ($locale == "pl_PL" || $locale == "pl") {
            $totalPrice *= 3.8;
            //$totalPrice = $this->convertCurrency($totalPrice, 'USD', 'PLN');
            $currency = "PLN";
        }

        $_user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());

//        try {
//            $userId = $_user->getUser()->getId();
//        } catch (NotNullConstraintViolationException $e){
//            $e->getMessage();
//        }

        $userId = $_user->getId();

        $qb = $orderRepository->createQueryBuilder('o')
            ->setParameter('q', '' . $userId . '')
            ->andWhere('o.user = :q');

        $query = $qb->getQuery(); //    ->getResult();

        $paginator = $this->get('knp_paginator');
        $orders = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return new JsonResponse([
                'user' => $user,
                'orders' => $orders,
                'totalPrice' => $totalPrice,
                'currency' => $currency
        ]);
//        return $this->render("orders/index.html.twig", array(
//            'user' => $user,
//            'orders' => $orders,
//            'totalPrice' => $totalPrice,
//            'currency' => $currency
//        ));
    }
}