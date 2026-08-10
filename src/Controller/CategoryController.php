<?php
/**
 *   CategoryController.php 
 *      Controleur de l'entité Category (categorie)
 * 
 *      Role:
 *          Enregistrer, modifier ou effacer une categorie, lister les categories
 */
namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/category')]
final class CategoryController extends AbstractController
{
    /** 
     *  Rôle :
     *     Préparer l'affichage de la page principale des articles
     *
     *  Retour :
     *     Response - Template de la page principale des articles
     */
    #[Route(name: 'app_category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }
    /** 
     *  Rôle :
     *     Traite la creation ou l'affichage du formulaire de creation ou redirectionnement vers la liste des categorie
     *  
     *  Retour :
     *     Response - Template de la page de modification de categorie ou redirection vers la liste des categories
     */
    #[Route('/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }
    /** 
     *  Rôle :
     *     Traite la modification ou l'affichage du formulaire de modification ou redirectionnement vers la liste des categorie
     *
     *  Paramètres :
     *      $id - Id de la categorie a modifier
     *      $form - Les donnés du formulaire envoyés (libelle)
     * 
     *  Retour :
     *     Response - Template de la page de modification de categorie ou redirection vers la liste des categories
     */
    #[Route('/{id}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }
    /** 
     *  Rôle :
     *     Traiter l'action d'effacer une categorie et redirectionne vers la liste des categories
     *
     *  Paramètres :
     *      $id - Id de la categorie a effacer
     * 
     *  Retour :
     *     Response - Redirection vers la liste des categories
     */
    #[Route('/{id}', name: 'app_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        // Verification du token delete pour protection CSRF
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
