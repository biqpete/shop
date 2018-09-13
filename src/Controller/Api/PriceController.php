<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/")
 * Class PriceController
 * @package App\Controller\orders
 */
class PriceController extends Controller
{
    private $price;

    /**
     * @return double
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param double $price
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }

    public function calculate($cpu=null,$ram=null,$hdd=null,$screen=null)
    {
        $price = 0;

        switch($cpu){
            case null:
                break;
            case 'i3':
                $price += 200;
                break;
            case 'i5':
                $price += 400;
                break;
            case 'i7':
                $price += 600;
                break;
        }

        switch($ram){
            case null:
                break;
            case 8:
                $price += 200;
                break;
            case 16:
                $price += 400;
                break;
            case 32:
                $price += 600;
                break;
        }

        switch($hdd){
            case null:
                break;
            case 128:
                $price += 200;
                break;
            case 256:
                $price += 400;
                break;
            case 512:
                $price += 600;
                break;
        }

        switch($screen){
            case null:
                break;
            case 10:
                $price += 200;
                break;
            case 13:
                $price += 400;
                break;
            case 15:
                $price += 600;
                break;
        }

        return $price;
    }
}