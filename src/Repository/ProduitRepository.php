<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function findPublicPaginated(?int $categoryId, ?int $sousCategorieId, string $searchTerm, int $page = 1, int $limit = 4): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);

        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.sousCategorie', 'sc')
            ->leftJoin('sc.category', 'c')
            ->addSelect('sc', 'c')
            ->andWhere('p.estActif = :actif')
            ->setParameter('actif', true)
            ->orderBy('p.id', 'DESC');

        if ($categoryId !== null && $categoryId > 0) {
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($sousCategorieId !== null && $sousCategorieId > 0) {
            $qb->andWhere('sc.id = :sousCategorieId')
                ->setParameter('sousCategorieId', $sousCategorieId);
        }

        if ($searchTerm !== '') {
            $qb->andWhere('LOWER(p.nom) LIKE :search OR LOWER(p.marque) LIKE :search OR LOWER(p.reference) LIKE :search OR LOWER(p.description) LIKE :search OR LOWER(sc.nom) LIKE :search OR LOWER(c.nom) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($searchTerm) . '%');
        }

        $total = (int) (clone $qb)
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $totalPages = max(1, (int) ceil($total / $limit));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $items = (clone $qb)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return [
            'items'       => $items,
            'total'       => $total,
            'page'        => $page,
            'limit'       => $limit,
            'total_pages' => $totalPages,
        ];
    }

    public function findAdminPaginatedBySousCategorie(?int $sousCategorieId, int $page = 1, int $limit = 5): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);

        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.sousCategorie', 'sc')
            ->addSelect('sc')
            ->orderBy('p.id', 'DESC');

        if ($sousCategorieId !== null && $sousCategorieId > 0) {
            $qb->andWhere('sc.id = :sousCategorieId')
                ->setParameter('sousCategorieId', $sousCategorieId);
        }

        $total = (int) (clone $qb)
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $totalPages = max(1, (int) ceil($total / $limit));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $items = (clone $qb)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => $totalPages,
        ];
    }

//    /**
//     * @return Produit[] Returns an array of Produit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Produit
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
