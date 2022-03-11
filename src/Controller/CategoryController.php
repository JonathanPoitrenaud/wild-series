<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Program;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/category', name: 'category_')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $categories = $doctrine->getRepository(Category::class)->findAll();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, CategoryRepository $categoryRepository): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categoryRepository->add($category);
            $this->addFlash('success', 'You add a new category');

            return $this->redirectToRoute('category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('category/new.html.twig', [
            'category'  => $category,
            'form'      => $form,
        ]);
    }

    #[Route('/show/{category}', name: 'show')]
    public function show(ManagerRegistry $doctrine, Category $category): Response
    {

        if (!$category) {
            throw $this->createNotFoundException(
                'No Tv show '.$category.' found.'
            );
        }

        $programs = $doctrine->getRepository(Program::class)
            ->findBy(
                ['category' => $category],
                ['id'       => 'ASC'],
        );
        dump($programs);

        return $this->render('category/show.html.twig', [
            'category'  => $category,
            'programs'  => $programs,
        ]);
    }
}
