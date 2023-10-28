<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


class BookController extends AbstractController
{
    #[Route('/book', name: 'app_book')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }

    #[Route ('/book/create', name: 'bookCreate')]
    public function create(ManagerRegistry $managerRegistry, Request $request): Response
    {
        // 1: Create a new student instance
        $book = new Book();

        // 2.1: Create the form
        $form = $this->createForm(BookType::class, $book);

        // 2.2: Handle form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 3: Persist and flush the book entity
            $entityManager = $managerRegistry->getManager();
            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('bookFetch');
        }

        // Always pass the 'form' variable to the template, even if the form is not submitted
        return $this->render('book/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/book/fetch',name:'bookFetch')]
    public function get (BookRepository $bookRepository)
    {
        $books = $bookRepository->findAll();

        $publishedBooks = $bookRepository->findPublishedBooks();
        $unpublishedBooks = $bookRepository->findBy(['published' => false]);

        $publishedCount = count($publishedBooks);
        $unpublishedCount = count($unpublishedBooks);
        return $this->render('book/fetch.html.twig', [
            'publishedBooks' => $publishedBooks,
            'unpublishedBooks' => $unpublishedBooks,
            'publishedCount' => $publishedCount,
            'unpublishedCount' => $unpublishedCount,
            'books'=>$books,
        ]);
    }
    #[Route('/book/edit/{ref}',name:'bookEdit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, $ref): Response
    {
        $book = $entityManager->getRepository(Book::class)->find($ref);

        if (!$book) {
            throw $this->createNotFoundException('Aucun livre trouvé pour cet réference');
        }

// Check if the request method is POST (indicating form submission)
        if ($request->isMethod('POST')) {
            // Replace 'name' with your form field names
            $newRef = $request->request->get('ref');
            $newTitle = $request->request->get('title');
            $newPublicationDate = $request->request->get('publicationDate');
            $newPublished = $request->request->get('published');
            $newCategory = $request->request->get('category');
            $newAuthor = $request->request->get('author');

            // Update the book entity with the new data
            $book->setRef($newRef);
            $book->setTitle($newTitle);
            $book->setPublicationDate($newPublicationDate);
            $book->setPublished($newPublished);
            $book->setCategory($newCategory);
            $book->setAuthor($newAuthor);

            // Persist the changes to the database
            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('bookFetch'); // Redirect after successful update
        }

        return $this->render('book/edit.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/book/delete/{ref}',name:'bookDelete')]
    public function delete($ref,ManagerRegistry $manager,BookRepository $bookRepository){
        $book = $bookRepository->find($ref);
        $manager->getManager()->remove($book);
        $manager->getManager()->flush();
        return $this->redirectToRoute('bookFetch');
    }

    #[Route('/book/show/{ref}', name: 'bookShow')]
    public function show(Book $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }
    #[Route('/book/search', name: 'bookSearch')]
    public function search(Request $request, BookRepository $bookRepository)
    {
        $ref = $request->query->get('ref'); // Get the "ref" parameter from the query string

        if ($ref) {
            $book = $bookRepository->searchBookByRef($ref);
        } else {
            $book = null;
        }

        return $this->render('book/search.html.twig', [
            'book' => $book,
        ]);
    }
    #[Route('/booksByAuthor', name: 'booksListByAuthors')]
    public function booksListByAuthors(BookRepository $bookRepository): Response
    {
        // Use the custom repository method to get books sorted by authors
        $books = $bookRepository->booksListByAuthors();

        return $this->render('book/booksByAuthor.html.twig', [
            'books' => $books,
        ]);
    }
    #[Route('/books/published', name: 'list_books_published_before_year')]
    public function listBooksPublishedBeforeYear(BookRepository $bookRepository): Response
    {
        $year = 2023; // Change this to your desired year
        $books = $bookRepository->BooksPublishedBeforeYear($year);

        return $this->render('book/listBooks.html.twig', [
            'books' => $books,
            'year' => $year,
        ]);
    }
    #[Route('/books/romance', name: 'romanceBooks')]
    public function countRomanceBooks(BookRepository $bookRepository): Response
    {
        $count = $bookRepository->RomanceBooks();

        return $this->render('book/romanceBooks.html.twig', [
            'count' => $count,
        ]);
    }
    #[Route('/books/publishedDates', name: 'publishedDates')]
    public function booksPublishedBetweenDates(BookRepository $bookRepository): Response
    {
        $startDate = new \DateTime('2014-01-01');
        $endDate = new \DateTime('2018-12-31');
        $books = $bookRepository->findBooksPublishedBetweenDates($startDate, $endDate);

        return $this->render('book/publishedDates.html.twig', [
            'books' => $books,
        ]);
    }

}
