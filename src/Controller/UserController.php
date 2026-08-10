<?php
/**
 *   UserController.php 
 *      Controleur pour l'entité des utilisateurs (User) 
 *  
 *      Role:
 *          Préparer l'affichage des pages d'utilisateur (Mon compte, liste d'utilisateurs, modification, banir, effacer)
 */
namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class UserController extends AbstractController
{
    /** 
     *  Rôle :
     *     Préparer l'affichage de la page principal de l'utilisateur (mon espace)
     *
     *  Retour :
     *     Response - Template de la page principal de l'utilisateur (mon espace)
     */
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $articlesPublies = $articleRepository->getMyArticles($user, true);
        $articlesNonPublies = $articleRepository->getMyArticles($user, false);
        return $this->render('user/index.html.twig', [
            'user' => $user,
            'articlesPublies' => $articlesPublies,
            'articlesNonPublies' => $articlesNonPublies
        ]);
    }
    /** 
     *  Rôle :
     *     Preparer l'affichage de la liste d'utilisateurs
     *
     *  Retour :
     *     Response - Template de la liste d'utilisateurs
     */

    #[Route('/list', name: 'app_user_list')]
    public function list(UserRepository $userRepository): Response
    {
        return $this->render('user/list.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }
    /** 
     *  Rôle :
     *     Préparer l'affichage de la page de modification d'utilisateur
     *
     *  Paramètres :
     *      $id - Id de l'utilisateur a modifier
     *      $form - Les donnés du formulaire envoyés (email, pseudo, motdepasse, roles)
     * 
     *  Retour :
     *     Response - Template de la page de modification d'utilisateur ou redirection espace mon compte
     */
    #[Route('/edit/{id}', name: 'app_user_edit', defaults: ['id' => null])]
    public function edit(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, ?int $id = null): Response
    {
         // Pour eviter l'erreur de l'IDE:
        /** @var User $user */

        if ($id && $this->isGranted("ROLE_ADMIN")) {
            $user = $userRepository->find($id);
        } else {
            $user = $this->getUser();
        }
  
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string|null $motdepasse */
            $motdepasse = $form->get('motdepasse')->getData();

            if (!empty($motdepasse)) {
                $user->setPassword($userPasswordHasher->hashPassword($user, $motdepasse));
            }
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form,
            'user' => $user
        ]);
    }
    /** 
     *  Rôle :
     *     Traiter l'action de bannir ou debannir un utilisateur et le redirectionement vers la page de liste d'utilisateurs
     *
     *  Paramètres :
     *      $id - Id de l'utilisateur a banir
     * 
     *  Retour :
     *     Response - Redirection vers la page de liste d'utilisateurs
     */
    #[Route('/ban/{id}', name: 'app_user_ban', methods: ['POST'])]
    public function ban(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        return $this->redirectToRoute('app_user_list', [], Response::HTTP_SEE_OTHER);
    }
    /** 
     *  Rôle :
     *     Traiter l'action d'effacer un compte et redirection vers la page de la liste d'utilisateurs ou la page principale
     *
     *  Paramètres :
     *      $id - Id de l'utilisateur a effacer
     * 
     *  Retour :
     *     Response - Redirection vers la page de liste d'utilisateurs ou la page principale
     */
    #[Route('/delete/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }
        if ($this->isGranted("ROLE_ADMIN")) {
            return $this->redirectToRoute('app_user_list', [], Response::HTTP_SEE_OTHER);
        } else {
            return $this->redirectToRoute('app_index', [], Response::HTTP_SEE_OTHER);
        }
    }
}
