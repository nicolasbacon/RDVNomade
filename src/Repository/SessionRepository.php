<?php

namespace App\Repository;

use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Session|null find($id, $lockMode = null, $lockVersion = null)
 * @method Session|null findOneBy(array $criteria, array $orderBy = null)
 * @method Session[]    findAll()
 * @method Session[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }


    public function findTenSessions()
    {
        $qb = $this->createQueryBuilder('s');

        $qb ->addSelect('s.id')
            ->addSelect('s.name')
            ->addSelect('s.enable')
            ->addSelect('s.synchrone')
            ->addSelect('s.dateEndSession')
            ->addSelect('s.timeAlert')
            ->orderBy('s.id', 'DESC')
            ->setMaxResults(10)
            ;
        return $qb->getQuery()->getResult();
    }

    public function findLast()
    {
        $qb = $this->createQueryBuilder('s');

        $qb ->orderBy('s.id', 'ASC')
            ->setMaxResults(1)
        ;
        return $qb->getQuery()->getResult();
    }

    // /**
    //  * @return Session[] Returns an array of Session objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Session
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
