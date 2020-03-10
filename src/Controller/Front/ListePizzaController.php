<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ListePizzaController extends AbstractController
{
    public function index()
    {
        return $this->render('front/list_pizza/index.html.twig');
    }
}
