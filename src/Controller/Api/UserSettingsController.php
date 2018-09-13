<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 13/09/2018
 * Time: 10:35
 */

namespace App\Controller\Api;


use App\Entity\User;
use App\Form\EditUserType;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/")
 * Class LocaleController
 * @package App\Controller\Api
 */
class UserSettingsController extends ApiController
{
    /**
     * @Route("settings", name="userSettings" )
     */
    public function changeLocale(Request $request)
    {
        $_user = $this->getUser();
        $user= $this->getDoctrine()->getRepository(User::class)->find($_user);

        $form = $this->createForm(EditUserType::class,$user);
        $this->get('serializer')->deserialize($request->getContent(), User::class, 'json', ['object_to_populate' => $user]);

        $formData = $form->setData($user);
        $form->submit($formData);

        if($form->isSubmitted())
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }

        $context = new SerializationContext();
        $context
            ->setSerializeNull(true)
            ->setGroups(['api']);

        return new JsonResponse([
            'user' => json_decode($this->get('jms_serializer')->serialize($user,'json', $context)),
        ]);
    }
}