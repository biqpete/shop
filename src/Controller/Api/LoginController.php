<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 06/09/2018
 * Time: 11:18
 */

namespace App\Controller\Api;


use App\Entity\User;
use App\Form\LoginType;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/api/")
 * Class LoginController
 * @package App\Controller\Api
 */
class LoginController extends ApiController
{
    /**
     * @Route("/", name="login", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @SWG\Post(
     *      path="/api/login/",
     *      produces={"appliacation/json"},
     *      description="Login",
     *      operationId="login",
     *      summary="Login user with credentials",
     *      tags={"Auth"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Json content for user",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="email", type="string", example="email@test.com"),
     *              @SWG\Property(property="password", type="string", example="zaq12wsx")
     *              )
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="Success",
     *          @SWG\Schema(
     *              @SWG\Property(property="token", type="string", example="Authorization token"),
     *              @SWG\Property(property="status", type="string", example="Response status information"),
     *              @SWG\Property(property="errors", type="array", example="['error1', 'error2']",
     *                  @SWG\Items(
     *                      type="string",
     *                      description="error message"
     *                  )
     *              )
     *          )
     *      )
     * )
     *
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException
     */
    public function login(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $token = '';
        $user = new User();
        $form = $this->createForm(LoginType::class);
        $form->submit(json_decode($request->getContent(), true));
        $this->get('serializer')->deserialize($request->getContent(), User::class, 'json', ['object_to_populate' => $user]);
        $errors = [];
        if ($form->isSubmitted()) {
                /** @var User $userFromDatabase */
                $userFromDatabase = $this->getDoctrine()->getRepository(User::class)->findOneBy(['isActive' => true, 'email' => $user->getEmail()]);
                if (!empty($userFromDatabase)) {
                    if ($passwordEncoder->isPasswordValid($userFromDatabase, $user->getPassword())) {
                        $token = $this->jwtAuth->encode(['username' => $userFromDatabase->getUsername(), 'exp' => time() + 604800]);
                        return new JsonResponse(['status' => 'ok', 'token' => $token, 'message' => '', 'errors' => $errors]);
                    } else {
                        $errors['password'] = 'Wrong password.';
                    }
                } else {
                    $errors['email'] = 'This user does not exist.';
                }
            return new JsonResponse(['status' => 'validation', 'token' => $token, 'message' => '', 'errors' => $errors]);
        }
    }
}
