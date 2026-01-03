<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }
    public function findBySupplier(?int $supplierId)
    {
        $qb = $this->createQueryBuilder('p')
            ->join('p.supplier', 's');

        if ($supplierId) {
            $qb->andWhere('s.id = :id')
                ->setParameter('id', $supplierId);
        }

        return $qb;
    }

}
