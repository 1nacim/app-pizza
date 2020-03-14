<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class ListePizzaController
 * @package App\Controller\Front
 */
class ListePizzaController extends AbstractController
{
    /**
     * @return Response
     */
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

    /**
     * @param Request $request
     * @return Response|null
     */
    public function ajouterPizzaAuPanier(Request $request)
    {
        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $pizzaRepository = $em->getRepository('App:Pizza');
            $pizzaRepository->find($request->get('id'));
        }

        return new Response(null, 400);
    }
}
