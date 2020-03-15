<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController extends AbstractController
{
    /**
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Vérifie si le visiteur n'est pas déjà connecté, si c'est le cas, redirection vers l'index de l'administration
        if ($this->getUser()) {
            return $this->redirectToRoute('easyadmin');
        }

        // Récupération des erreurs d'authentification s'il y en a
        $error = $authenticationUtils->getLastAuthenticationError();
        // Récupération du dernier email entré par le visiteur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
