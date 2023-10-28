<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($ref, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

//    /**
//     * @return Book[] Returns an array of Book objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Book
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
// BookRepository.php
    public function findPublishedBooks()
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.published = :published')
            ->setParameter('published', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function searchBookByRef($ref)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.ref = :ref')
            ->setParameter('ref', $ref)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function booksListByAuthors()
    {
        return $this->createQueryBuilder('b')
            ->join('b.author', 'a')
            ->orderBy('a.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function BooksPublishedBeforeYear(int $year)
    {
        return $this->createQueryBuilder('b')
            ->select('b')
            ->leftJoin('b.author', 'a')
            ->where('b.publicationDate < :year')
            ->andWhere('a.nb_books > 10')
            ->setParameter('year', $year . '-01-01') // Convert to the first day of the year
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function RomanceBooks()
    {
        return $this->createQueryBuilder('b')
            ->select('COUNT(b.ref)')
            ->where('b.category = :category')
            ->setParameter('category', 'Romance')
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function findBooksPublishedBetweenDates(\DateTime $startDate, \DateTime $endDate)
    {
        return $this->createQueryBuilder('b')
            ->where('b.publicationDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }
}
