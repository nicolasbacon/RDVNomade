<?php

namespace App\Repository;

use App\Entity\PlayerAsset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PlayerAsset|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerAsset|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerAsset[]    findAll()
 * @method PlayerAsset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerAssetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerAsset::class);
    }

    // /**
    //  * @return PlayerAsset[] Returns an array of PlayerAsset objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PlayerAsset
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
