<?php

namespace App\Controller\Front;

use App\Entity\Pizza;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ListePizzaController extends AbstractController
{
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $pizzaRepository = $em->getRepository('App:Pizza');
        $pizzas = $pizzaRepository->findAll();

        return $this->render(
            'front/list_pizza/index.html.twig',
            [
                'pizzas' => $pizzas
            ]
        );
    }
}
