<?php
/**
 *   SecurityController.php 
 *      Controleur de securité pour la connection/deconnection d'utilisateur
 * 
 *      Role:
 *          Préparer l'affichage de la page de conection et déconnection de l'utilisateur et traiter la connection/déconection et la redirection vers
 *              la page de l'utilisateur ou page principale
 */
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /** 
     *  Rôle :
     *     Préparer l'affichage de la page de conection de l'utilisateur et traiter la connection et la redirection vers 
     *          la page de l'utilisateur
     *
     *  Paramètres :
     *      $error - Les erreurs d'autentication
     *      $lastUsername - Dernier email utilisée dans le formulaire
     *      $form - Les donnés du formulaire envoyés (email, password)
     *
     *  Retour :
     *     Response - Formulaire de connection ou page mon compte si succés
     */

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
    /** 
     *  Rôle :
     *    Préparer la deconnection de l'utilisateur et l'affichage de la page d'accueil
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
