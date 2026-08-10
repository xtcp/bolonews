<?php
/**
 *   CommentController.php 
 *      Controleur de l'entité Comment (commentaires)
 * 
 *      Role:
 *          Enregistrer un nouveau commentáire
 */
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class CommentController extends AbstractController
{
    /** 
     *  Rôle :
     *     Traiter l'ajout d'un commentaire et l'affichage de la page de l'article
     *
     *  Paramètres :
     *      form - Les données de requête du formulaire envoyés (article.id texte)
     * 
     *  Retour :
     *     Response - Redirectioner vers la page de l'article
     */
    #[Route('/comment/new/{id}', name: 'app_comment_new', methods: ['POST'])]
    public function new(Article $article, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Pour eviter l'erreur de l'IDE:
        /** @var User $user */
        $user = $this->getUser();
        // Verifier si l'utilisateur est bani et bloquer l'accés
        if ($user->isBanni()) {
            throw new AccessDeniedHttpException("Vous ne pouvez pas effectuer cette action parce que votre compte est bani");
        }
        // Verification du token comment pour protection CSRF
        if (!$this->isCsrfTokenValid('comment' . $article->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        if ($user) {
            $comment = new Comment();
            $texte = $request->request->get('texte');
            $comment->setTexte($texte);
            $comment->setDateheure(new DateTime());
            $comment->setArticle($article);
            $comment->setUser($user);

            $entityManager->persist($comment);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
    }
}
