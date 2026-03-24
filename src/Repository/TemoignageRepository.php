<?php

namespace App\Repository;

use App\Entity\Temoignage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Temoignage>
 */
class TemoignageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Temoignage::class);
    }

    /**
     * @return Temoignage[]
     */
    public function findActiveForHome(int $limit = 6): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.produit', 'p')
            ->addSelect('p')
            ->andWhere('t.estActif = :actif')
            ->setParameter('actif', true)
            ->orderBy('t.ordreAffichage', 'ASC')
            ->addOrderBy('t.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
