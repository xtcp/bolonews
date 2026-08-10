<?php
/**
 *   AppController.php 
 *      Controleur de l'application
 * 
 *      Role:
 *          Préparer l'affichage de la page principal
 */
namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppController extends AbstractController
{
    /** 
     *  Rôle :
     *     Préparer l'affichage de la page principal
     *
     *  Retour :
     *     Response - Template de la page principale avec l'article a la une et les derniéres articles
     */
    #[Route('/', name: 'app_index')]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('app/index.html.twig', [
           'featuredArticle' => $articleRepository->getFeatured(),
           'latestArticles' => $articleRepository->getLatest(4)
        ]);
    }
}
