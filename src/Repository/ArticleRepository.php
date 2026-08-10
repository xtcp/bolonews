<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }
    public function getFeatured(): Article|null
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.likes', 'l')
            ->addSelect('COUNT(l.id) AS HIDDEN likeCount')
            ->andWhere('a.publie = true')
            ->groupBy('a.id')
            ->orderBy('likeCount', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function getMyArticles(User $user, bool $publie = false): array
    {
        return $this->createQueryBuilder('a')
        ->where('a.auteur = :user AND a.publie = :publie')
        ->setParameter('publie', ($publie ? 1 : 0))
        ->setParameter('user', $user)
        ->orderBy('a.id', 'DESC')
        ->getQuery()
        ->getResult();
    }
    public function search(?string $query, ?int $category): array
    {
        if ($category) {
            return $this->createQueryBuilder('a')
            ->leftJoin('a.categorie', 'c')
            ->where('(a.titre LIKE :q OR a.texte LIKE :q OR a.chapeau LIKE :q) AND c.id = :category AND a.publie = 1')
            ->setParameter('q', '%' . $query . '%')
            ->setParameter('category', $category)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
        } else {
            return $this->createQueryBuilder('a')
            ->leftJoin('a.categorie', 'c')
            ->where('(a.titre LIKE :q OR a.texte LIKE :q OR a.chapeau LIKE :q OR c.libelle LIKE :q) AND a.publie = 1')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
        }
    }
    public function getLatest(int $limit): array
    {
        return $this->createQueryBuilder('a')
        ->where('a.publie = 1')
        ->setMaxResults($limit)
        ->orderBy('a.dateheure_creation', 'DESC')
        ->getQuery()
        ->getResult();
    }
    //    /**
    //     * @return Article[] Returns an array of Article objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Article
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
