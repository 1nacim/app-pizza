<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ListePizzaController
 * @package App\Controller\Front
 */
class ListePizzaController extends AbstractController
{
    /**
     * Méthode permettant d'afficher l'index qui n'est autre que la liste des pizzas
     *
     * @return Response
     */
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $pizzaRepository = $em->getRepository('App:Pizza');

        // Récupération de toutes les pizzas
        $pizzas = $pizzaRepository->findAll();

        return $this->render(
            'front/list_pizza/index.html.twig',
            [
                'pizzas' => $pizzas
            ]
        );
    }

    /**
     * Méthode permettant d'ajouter un article (une pizza) au panier
     *
     * @param Request $request
     * @return Response
     */
    public function ajouterPizzaAuPanier(Request $request): Response
    {
        // Traitement uniquement si la méthode HTTP envoyée est de type POST
        if ($request->isMethod('POST')) {
            // Récupération de l'ID de la pizza ajoutée dans le panier
            $idPizza = $request->get('id');

            $response = new Response();

            /*
             * Vérifie si le cookie "idPizzas" existe
             * - S'il n'existe pas, on initialise un tableau et on lui passe une valeur, l'ID de la pizza ajoutée au panier
             * - S'il existe, on récupère son contenu est on ajoute au tableau l'ID de la pizza ajoutée au panier
             */
            if ($request->cookies->get('idPizzas') == null) {
                // Ajout de la pizza ajoutée au panier
                $idPizzas[] = $idPizza;
            } else {
                // Désérialisation du contenu du cookie "idPizzas"
                $idPizzas = unserialize($request->cookies->get('idPizzas'));
                // Ajout de la pizza ajoutée au panier
                $idPizzas[] = $idPizza;
            }

            // Création/Recréation du cookie "idPizzas" avec en valeur le tableau sérialisé contenant les IDs de pizzas (durée de vie: 1 journée)
            $cookie = new Cookie(
                'idPizzas',
                serialize($idPizzas),
                time() + (24 * 60 * 60)
            );

            // Ajout du cookie au navigateur
            $response->headers->setCookie($cookie);

            // Création/Recréation du cookie "nbPizzas" avec en valeur le nombre de pizzas contenues dans le tableau "idPizzas"
            $cookie = new Cookie(
                'nbPizzas',
                count($idPizzas),
                time() + (24 * 60 * 60)
            );

            // Ajout du cookie au navigateur
            $response->headers->setCookie($cookie);

            $response->setContent(null);
            $response->setStatusCode(200);
            return $response;
        }

        return new Response(null, 400);
    }
}
