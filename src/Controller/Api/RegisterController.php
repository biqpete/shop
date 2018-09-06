<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 06/09/2018
 * Time: 11:19
 */

namespace App\Controller\Api;


use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class RegisterController extends Controller
{
    /**
     * @Route("/api/register", name="register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, AuthenticationUtils $utils, \Swift_Mailer $mailer)
    {
        $error = $utils->getLastAuthenticationError();
        $lastUserName = $utils->getLastUsername();
        // 1) build the form
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

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

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);
            $username = $user->getUsername();
            // 4) save the User!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);

            try {
                $entityManager->flush();
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash(
                    'error',
                    'This username is already taken.'
                );

                return $this->redirectToRoute('register');
            }

            $this->addFlash(
                'notice',
                'Activation email has been sent. Check your email to activate the account'
            );

            return $this->redirectToRoute('login');
        }

        return $this->render(
            'security/register.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
            'last_username' => $lastUserName
        ]);
    }
}