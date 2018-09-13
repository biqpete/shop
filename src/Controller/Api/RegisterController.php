<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Form\LoginType;
use App\Form\RegisterType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/api/")
 * Class RegisterController
 * @package App\Controller\Api
 */
class RegisterController extends Controller
{
    /**
     * @Route("/register", name="api_register", methods="POST")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return JsonResponse
     * @SWG\Post(
     *      path="/api/register",
     *      produces={"application/json"},
     *      description ="Register User",
     *      operationId="register",
     *      summary="User registration process",
     *      tags={"Auth"},
     *      @SWG\Parameter(
     *          name="register",
     *          in="body",
     *          description="Json content for user",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="user", type="object",
     *                  @SWG\Property(property="email", type="string", example="test@test.com"),
     *                  @SWG\Property(property="username", type="string", example="John"),
     *                  @SWG\Property(property="password", type="string", example="zaq12222")
     *              ),
     *           )
     *      ),
     * @SWG\Response(
     *     response="200",
     *     description="success",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="status", type="string", example="Response status information"),
     *              @SWG\Property(property="Token", type="string", example="Authorization token"),
     *              @SWG\Property(property="errors", type="string", example="['error1', 'error2']"),
     *              ),
     *              description="Connection successful",
     *          ),
     * @SWG\Response(
     *          response="400",
     *          description="Bad Request",
     *     )
     * )
     * @throws \Exception
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, AuthenticationUtils $utils, \Swift_Mailer $mailer)
    {
        $error = $utils->getLastAuthenticationError();
        $lastUserName = $utils->getLastUsername();

        $user = new User();

        $form = $this->createForm(RegisterType::class, $user);
        $form->submit(json_decode($request->getContent(), true));
        $this->get('serializer')->deserialize($request->getContent(), User::class, 'json', ['object_to_populate' => $user]);

        if ($form->isSubmitted()) {

            $activationHash = md5(random_bytes(25));
            $user->setHash($activationHash);
            $user->setIsActive(false);

            $message = (new \Swift_Message("Activate your account on Peter's Shop"))
                ->setFrom('petermailer777@gmail.com')
                ->setTo($user->getEmail())
//                    ->setTo("ochenx@gmail.com")
                ->setBody("Hello ".ucfirst($user->getUsername()).'! '.
                    "Click the link below to activate your account ".
                    "http://localhost:8000/auth/".$activationHash
                )
            ;
            $mailer->send($message);

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());

            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);

            $username = $user->getUsername();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);

            try {
                $entityManager->flush();
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash(
                    'error',
                    'This username is already taken.'
                );
                return new JsonResponse(['status' => 'nok','username' => "unknown", 'password' => 'unknown']);
            }
            $this->addFlash(
                'notice',
                'Activation email has been sent. Check your email to activate the account'
            );
            return new JsonResponse(['status' => 'email sent','username' => $username, 'password' => $password]);
        }
        return new JsonResponse(['status' => 'nok','username' => 'unknown', 'password' => 'unknown']);
    }

    /**
     * @Route("/auth/{slug}", name="auth")
     */
    public function activateUser($slug, \Swift_Mailer $mailer)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(
            [
                'hash' => $slug
            ]
        );
        if(!empty($user))
        {
            $user->setIsActive(true);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $username = $user->getUsername();
            $message = (new \Swift_Message('Hello '.ucfirst($username).'!'))
                ->setFrom('petermailer777@gmail.com')
                ->setTo($user->getEmail())
//                    ->setTo("ochenx@gmail.com")
                ->setBody("Hello ".ucfirst($username).'! '.
                    "You're now registered on Peter's Shop! Use your credentials to login.")
            ;
            $mailer->send($message);

            return new JsonResponse(['status' => 'ok']);
        }
        return new JsonResponse(['status' => 'nok']);
    }
}
