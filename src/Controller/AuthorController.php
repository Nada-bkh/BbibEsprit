<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Student;
use App\Form\AuthorSearchType;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    private $authorSearchRepository;

    public function __construct(AuthorRepository $authorSearchRepository)
    {
        $this->authorSearchRepository = $authorSearchRepository;
    }

    #[Route('/author', name: 'app_author')]
    public function index(): Response
    {
        return $this->render('author/index.html.twig', [
            'controller_name' => 'AuthorController',
        ]);
    }

    #[Route ('/create', name: 'create')]
    public function create(ManagerRegistry $managerRegistry, Request $request): Response
    {
        // 1: Create a new student instance
        $author = new Author();

        // 2.1: Create the form
        $form = $this->createForm(AuthorType::class, $author);

        // 2.2: Handle form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 3: Persist and flush the student entity
            $entityManager = $managerRegistry->getManager();
            $entityManager->persist($author);
            $entityManager->flush();

            return $this->redirectToRoute('fetch');
        }

        // Always pass the 'form' variable to the template, even if the form is not submitted
        return $this->render('author/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/fetch', name: 'fetch')]
    public function get(AuthorRepository $authorRepository)
    {
        $authors = $authorRepository->findAll();
        return $this->render('author/fetch.html.twig', [
            'authors' => $authors
        ]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete($id, ManagerRegistry $manager, AuthorRepository $authorRepository)
    {
        $author = $authorRepository->find($id);
        $manager->getManager()->remove($author);
        $manager->getManager()->flush();
        return $this->redirectToRoute('fetch');
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        $author = $entityManager->getRepository(Author::class)->find($id);

        if (!$author) {
            throw $this->createNotFoundException('Aucun étudiant trouvé pour cet ID');
        }

        // Check if the request method is POST (indicating form submission)
        if ($request->isMethod('POST')) {
            $username = $request->request->get('username');// Replace 'name' with your form field name
            $email = $request->request->get('email');
            // Handle other form fields similarly

            // Update the student entity with the new data
            $author->setUsername($username);
            $author->setEmail($email);
            // Update other entity fields as needed

            // Persist the changes to the database
            $entityManager->persist($author);
            $entityManager->flush();

            return $this->redirectToRoute('fetch'); // Redirect after successful update
        }

        return $this->render('author/edit.html.twig', [
            'author' => $author,
        ]);
    }

    #[Route('/authors', name: 'authorListByEmail')]
    public function listAuthorsByEmail(AuthorRepository $authorRepository): Response
    {
        $authors = $authorRepository->listAuthorByEmail();

        return $this->render('author/fetch.html.twig', [
            'authors' => $authors,
        ]);
    }

    #[Route('/search', name: 'search')]
    public function search(Request $request): Response
    {
        $form = $this->createForm(AuthorSearchType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $minBookCount = $data['minBookCount'];
            $maxBookCount = $data['maxBookCount'];

            // Query your repository to find authors within the specified book count range
            $authors = $this->authorSearchRepository->findAuthorsByBookCountRange($minBookCount, $maxBookCount);

            // Render the view with the filtered authors
            return $this->render('author/search_results.html.twig', [
                'authors' => $authors,
            ]);
        }

        return $this->render('author/search_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/noBooks', name: 'deleteWithNoBooks')]
    public function deleteAuthorsWithNoBooks(AuthorRepository $authorRepository): Response
    {
        // Call the custom repository method to delete authors with nb_books = 0
        $authorRepository->deleteAuthorsWithNoBooks();

        // You can return a response or redirect to another page
        return $this->redirectToRoute('fetch'); // Replace 'some_route' with the route you want to redirect to
    }
}
