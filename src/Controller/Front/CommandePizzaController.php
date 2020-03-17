<?php

namespace App\Controller\Front;

use App\Entity\Adresse;
use App\Entity\Client;
use App\Entity\Commande;
use App\Entity\LigneDeCommande;
use App\Form\AdresseType;
use App\Form\ClientType;
use DateTime;
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
     * Méthode permettant d'afficher la vue contenant le récapitulatif de la commande
     *
     * @param Request $request
     * @return Response
     */
    public function recapitulatif(Request $request): Response
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
     * Méthode permettant de vider le panier en cours
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function viderPanier(Request $request): RedirectResponse
    {
        $response = new Response();
        $response->headers->clearCookie('idPizzas');
        $response->headers->clearCookie('nbPizzas');
        $response->sendHeaders();
        return $this->redirectToRoute('front_liste_pizza_index');
    }

    /**
     * Méthode permettant d'afficher la vue du formulaire de saisie des informations cliente
     *
     * @param Request $request
     * @return Response
     */
    public function informationsClient(Request $request): Response
    {
        // Récupération des sessions existantes
        $session = $request->getSession();

        // Vérifie que la session contenant le récapitulatif de la commande existe
        if (!$session->get('recapitulatif')) {
            return $this->redirectToRoute('front_commande_pizza_recapitulatif');
        }

        // Création du futur nouveau client pour créer le formulaire
        $client = new Client();
        // Création du formulaire client
        $clientForm = $this->createForm(ClientType::class, $client);

        // Récupération des données envoyées par l'utilisateur
        $clientForm->handleRequest($request);
        // Vérifie si le formulaire a bien été posté et est bien valide
        if ($clientForm->isSubmitted() && $clientForm->isValid()) {

            // Peuplement de l'objet avec les données issues du formulaire
            $client = $clientForm->getData();

            // Ajout du client en session
            $session->set('client', serialize($client));

            // Redirection vers la page de saisie des informations de livraison
            return $this->redirectToRoute('front_commande_pizza_infos_livraison');
        }

        return $this->render('front/commande_pizza/informations-client.html.twig', [
            'clientForm' => $clientForm->createView()
        ]);
    }

    /**
     * Méthode permettant d'afficher la vue du formulaire de saisie des informations de livraison
     *
     * @param Request $request
     * @return Response
     */
    public function informationsLivraison(Request $request): Response
    {
        // Récupération des sessions existantes
        $session = $request->getSession();

        // Vérifie que la session contenant le client de la commande existe
        if (!$session->get('client')) {
            return $this->redirectToRoute('front_commande_pizza_infos_client');
        }

        // Création de la future nouvelle adresse pour créer le formulaire
        $adresse = new Adresse();
        // Création du formulaire d'adresse de livraison
        $adresseForm = $this->createForm(AdresseType::class, $adresse, [
            'action' => $this->generateUrl('front_commande_pizza_termine')
        ]);

        return $this->render('front/commande_pizza/informations-livraison.html.twig', [
            'adresseForm' => $adresseForm->createView()
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function termine(Request $request): Response
    {
        // Création de la future nouvelle adresse pour créer le formulaire
        $adresse = new Adresse();
        // Création du formulaire d'adresse de livraison
        $adresseForm = $this->createForm(AdresseType::class, $adresse);

        // Récupération des données envoyées par l'utilisateur
        $adresseForm->handleRequest($request);
        // Vérifie si le formulaire a bien été posté et est bien valide
        if ($adresseForm->isSubmitted() && $adresseForm->isValid()) {

            // Récupération des sessions existantes
            $session = $request->getSession();
            // Récupération de l'entity manager
            $em = $this->getDoctrine()->getManager();

            // Peuplement de l'objet avec les données issues du formulaire
            $adresse = $adresseForm->getData();
            // Récupération du client
            $client = unserialize($session->get('client'));

            // Persistance des objets "Adresse" et "Client" et envoi en base de données
            $em->persist($adresse);
            $em->persist($client);
            $em->flush();

            // Récupération du récapitulatif de la commande
            $recapitulatif = unserialize($session->get('recapitulatif'));

            $ligneDeCommandes = [];
            $prixTotal = 0;

            // Parcours toutes les pizzas présentes dans la récapitulatif
            foreach ($recapitulatif as $item) {
                // Création d'une nouvelle ligne de commande pour chaque item
                $ligneDeCommande = new LigneDeCommande();
                $ligneDeCommande->setPizza($item['pizza'])
                    ->setQuantite($item['quantite']);

                // Ajout de la ligne dans la tableau des lignes de commande
                $ligneDeCommandes[] = $ligneDeCommande;
                // Mise à jour du prix total
                $prixTotal += $ligneDeCommande->getPizza()->getPrix() * $ligneDeCommande->getQuantite();
            }

            // Création d'une nouvelle commande
            $commande = new Commande();
            $commande->setEtatCommande(false)
                ->setClient($client)
                ->setAdresse($adresse)
                ->setPrixTotal($prixTotal)
                ->setDateCreation(new DateTime('now'));

            // Persistance de l'objet "Commande" et envoi en base de données
            $em->persist($commande);
            $em->flush();

            // Parcours des lignes de commande (pour mettre à jour l'ID commande)
            foreach ($ligneDeCommandes as $ligneDeCommande) {
                // Mise à jour de l'ID commande (une ligne de commande est liée à une commande
                $ligneDeCommande->setCommande($commande);
                // Persistance de l'objet "LigneDeCommande"
                $em->persist($ligneDeCommande);
            }

            // Envoi en base de données tous les objets "LigneDeCommande" précédement persistés
            $em->flush();

            // Affichage de la page indiquant à l'utilisateur que ça commande et bien passé
            return $this->render('front/commande_pizza/termine.html.twig');
        }
        // Si ce n'est pas le cas, on redirige l'utilisateur vers la page de saisie des informations de livraison
        else {
            return $this->redirectToRoute('front_commande_pizza_infos_livraison');
        }
    }
}
