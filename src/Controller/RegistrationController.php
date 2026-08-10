<?php
/**
 *   RegistrationController.php 
 *      Controleur de registre de compte d'utilisateur
 * 
 *      Role:
 *          Préparer l'affichage de la page de creation de compte de l'utilisateur,
 *              traite la creation de compte et la redirection vers le formulaire ou la page d'accueil si sucés
 */
namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    /** 
     *  Rôle :
     *     Préparer l'affichage de la page de creation de compte de l'utilisateur,
     *           traite la creation de compte et la redirection vers la page d'accueil
     *
     *  Paramètres :
     *      form - Les données de requête du formulaire envoyés (email, password, agreeTerms)
     * 
     *  Retour :
     *     Response - Template de la page de création d'utilisateur ou espace mon compte si succés
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $motdepasse */
            $motdepasse = $form->get('password')->getData();

            // hasher le mot de passe
            $user->setPassword($userPasswordHasher->hashPassword($user, $motdepasse));

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
