<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 10/09/2018
 * Time: 14:44
 */

namespace App\Controller\Api;


use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class ApiController extends Controller
{
    /**
     * serializer field
     *
     * @var Serializer
     */
    protected $serializer;

    /**
     * jwtAuth field
     *
     * @var JWTEncoderInterface
     */
    protected $jwtAuth;

    /**
     * ApiController constructor.
     * @param JWTEncoderInterface $jwtAuth
     */
    public function __construct(JWTEncoderInterface $jwtAuth, SerializerInterface $serializer)
    {
        $this->jwtAuth = $jwtAuth;
        $this->serializer = $serializer;
    }
}