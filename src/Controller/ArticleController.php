<?php
/**
 *   ArticleController.php 
 *      Controleur de l'entité Article (article)
 * 
 *      Paramètres:
 *          Config:
 *              image_directory - Le dossier des images uploadés
 *          Services:
 *              imageUploader - Le service d'upload d'images
 * 
 *      Role:
 *          Afficher, enregistrer, modifier, effacer, ou publier un article, rechercher, lister des articles
 */
namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/article')]
final class ArticleController extends AbstractController
{
    private ImageUploader $imageUploader;
    public function __construct(#[Autowire(param: 'image_directory')] string $imageDirectory) {
        $this->imageUploader = new ImageUploader($imageDirectory);
    }
    /** 
     *  Rôle :
     *     Traiter une rechercher et/ou préparer l'affichage de la page principale des articles
     *
     *  Paramètres :
     *      $search - Texte a rechercher
     *      $category - Categorie pour afficher les articles
     * 
     *  Retour :
     *     Response - Template de la page principale des articles
     */
    #[Route(name: 'app_article_index', methods: ['GET'])]
    public function index(Request $request, ArticleRepository $articleRepository, CategoryRepository $categoryRepository): Response
    {
        $search = $request->query->get('search');
        $category = $request->query->get('category');
 
        $articles = ($search || $category)
            ? $articleRepository->search($search, (int)$category)
            : $articleRepository->getLatest(6);
        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'categories' => $categoryRepository->findAll()
        ]);
    }
    /** 
     *  Rôle :
     *     Modifier ou créer un article
     *
     *  Paramètres :
     *      $request - Les données de la requête (formulaire)
     *      $article - L'article à modifier (vide pour créer)
     *      $entityManager - Gestionnaire de l'entité
     *      $isNew - Bool - Est un nouveau article
     * 
     *  Retour :
     *     Response - Template de la page de modification/création d'article ou redirection vers la page de l'article
     */
    private function editCreate(Request $request, Article $article, EntityManagerInterface $entityManager, bool $isNew = false): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $image = $form->get("image")->getData();
            if (!$image) {
                if ($isNew) $form->get("image")->addError(new FormError("l'image est obligatoire!"));
            } else {
                if ($isNew) {
                    $nomImage = $this->imageUploader->upload($image);
                } else {
                    $nomImage = $this->imageUploader->replace($image, $article->getImage());
                }
                $article->setImage($nomImage);
            }

            if ($form->isValid()) {
                if ($isNew) $entityManager->persist($article);
                $entityManager->flush();

                return $this->redirectToRoute('app_article_show', ['id' => $article->getId()], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('article/'.($isNew ? 'new' : 'edit').'.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }
    /** 
     *  Rôle :
     *     Traite la creation et prepare l'affichage de la page de creation d'article
     *
     *  Paramètres :
     *      $form - Les données de requête du formulaire envoyés (titre, chapeau, texte, image, categorie)
     * 
     *  Retour :
     *     Response - Template de la page de creation ou redirection vers la page de l'article si succés
     */
    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, bool $isNew = false): Response
    {
        $article = new Article();
        return $this->editCreate($request, $article, $entityManager, true);
    }
    /** 
     *  Rôle :
     *     Traite la modification et prepare l'affichage de la page de modification d'article
     *
     *  Paramètres :
     *      $form - Les données de requête du formulaire envoyés (titre, chapeau, texte, image, categorie)
     * 
     *  Retour :
     *     Response - Template de la page de modification ou redirection vers la page de l'article si succés
     */
    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ($article->getAuteur() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException("Vous n'avez pas des droits pour modifier cet article");
        }
        return $this->editCreate($request, $article, $entityManager);
    }
    /** 
     *  Rôle :
     *     Prépare l'affichage de la page de visualisation d'un article
     *
     *  Paramètres :
     *      $id - Id de l'article a afficher
     * 
     *  Retour :
     *     Response - Template de la page de visualisation d'un article
     */
    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }
    /** 
     *  Rôle :
     *     Traite l'action d'effacer un article et la redirection vers la page principale des articles
     *
     *  Paramètres :
     *      $id - Id de l'article a afficher
     * 
     *  Retour :
     *     Response - Redirectionement vers la page principale des articles
     */
    #[Route('/delete/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->getPayload()->getString('_token'))) {
            $user = $this->getUser();
            if ($article->getAuteur() !== $user && !$this->isGranted('ROLE_ADMIN')) {
                throw new AccessDeniedHttpException("Vous n'avez pas des droits pour effacer cet article");
            }
            $entityManager->remove($article);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }
    /** 
     *  Rôle :
     *     raite l'action de publier ou depublier un article et la redirection vers la page principale des articles
     *
     *  Paramètres :
     *      $id - Id de l'article a publier/depublier
     * 
     *  Retour :
     *     Response - Redirectionement vers la page principale des articles
     */
    #[Route('/publish/{id}', name: 'app_article_publish', methods: ['POST'])]
    public function publish(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('publish'.$article->getId(), $request->getPayload()->getString('_token'))) {
            $user = $this->getUser();
            if ($article->getAuteur() !== $user && !$this->isGranted('ROLE_ADMIN')) {
                throw new AccessDeniedHttpException("Vous n'avez pas des droits pour publier/depublier cet article");
            }
            $publie = $article->isPublie();
            $article->setPublie($publie ? 0 : 1);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_article_show', ['id' => $article->getId()], Response::HTTP_SEE_OTHER);
    }
    /** 
     *  Rôle :
     *     Traite l'action de de liker/unliker un article et l'affichage de la page de l'article
     *
     *  Paramètres :
     *      $id - Id de l'article a liker/deliker
     * 
     *  Retour :
     *     Response - Redirectionement vers la page principale des articles
     */
    #[Route('/like/{id}', name: 'app_article_toggle_like')]
    public function like(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $currentRoute = $request->getPayload()->getString('currentRoute');
    
        if ($this->isCsrfTokenValid('like'.$article->getId(), $request->getPayload()->getString('_token'))) {
            $user = $this->getUser();
            if ($article->getLikes()->contains($user)) {
                $article->removeLike($user);
            } else {
                $article->addLike($user);
            }
            $entityManager->flush();
        }
        return $this->redirectToRoute($currentRoute, ['id' => $article->getId()], Response::HTTP_SEE_OTHER);
    }
}
