<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class CommandePizzaController
 * @package App\Controller\Front
 */
class CommandePizzaController extends AbstractController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function recapitulatif(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $pizzaRepository = $em->getRepository('App:Pizza');

        /*
         * Tableau contenant le récupitulatif de la commande sous la forme :
         * $recapitulatif[$idPizza]['id']       => Contient l'ID de la pizza
         * $recapitulatif[$idPizza]['pizza']    => Contient la pizza
         * $recapitulatif[$idPizza]['quantite'] => Contient la quantité de la même pizza
         * $recapitulatif['prixTotal']          => Contient le prix total de la commande
         */
        $recapitulatif = [];
        $prixTotal = 0;

        // Vérifie si le tableau contenant les IDs de pizzas existe bien dans les cookies
        if ($idPizzas = unserialize($request->cookies->get('idPizzas'))) {

            // Parcours des IDs de pizza
            foreach ($idPizzas as $id) {
                /*
                 * Vérifie si l'ID de la pizza parcourue est dans le tableau contenant le récapitulatif de la commande
                 * Si c'est le cas, on ajoute incrémente 1 à la quantité dans le tableau pour la pizza ayant l'ID actuellement parcouru
                 * Sinon, on ajoute la nouvelle pizza au tableau
                 */
                if (array_key_exists($id, $recapitulatif)) {
                    // Incrémente 1 à la quantité pour la pizza ayant l'ID actuellement parcouru
                    $recapitulatif[$id]['quantite']++;
                    $prixTotal += $recapitulatif[$id]['prix'];
                } else {
                    // Récupération de la pizza via l'ID de pizza actuellement parcouru
                    $pizza = $pizzaRepository->find($id);
                    // Ajout de l'élément (id/pizzas/quantité/prix/prixTotal) dans le tableau
                    $recapitulatif[$id]['id'] = $id;
                    $recapitulatif[$id]['pizza'] = $pizza;
                    $recapitulatif[$id]['quantite'] = 1;
                    $recapitulatif[$id]['prix'] = $pizza->getPrix();
                    $prixTotal += $pizza->getPrix();
                }
            }

            // Démarrage des sessions
            $session = new Session();
            $session->start();

            // Ajout du tableau de récapitulatif en session
            $session->set('recapitulatif', serialize($recapitulatif));
        } else {
            $recapitulatif = null;
        }

        return $this->render('front/commande_pizza/index.html.twig', [
            'recapitulatif' => $recapitulatif,
            'prixTotal' => $prixTotal
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function viderPanier(Request $request)
    {
        $response = new Response();
        $response->headers->clearCookie('idPizzas');
        $response->headers->clearCookie('nbPizzas');
        $response->sendHeaders();
        return $this->redirectToRoute('front_liste_pizza_index');
    }

    public function commander()
    {
        // Démarrage des sessions
        $session = new Session();
        $session->start();

        // Récupération du tableau de récapitulatif de la commande en session
        $commande = unserialize($session->get('recapitulatif'));

        return $this->render('front/commande_pizza/informations-client.html.twig');
    }
}
