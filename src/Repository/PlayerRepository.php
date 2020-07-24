<?php

namespace App\Repository;

use App\Entity\Player;
use App\Entity\PlayerEnigma;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Player|null find($id, $lockMode = null, $lockVersion = null)
 * @method Player|null findOneBy(array $criteria, array $orderBy = null)
 * @method Player[]    findAll()
 * @method Player[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerRepository extends ServiceEntityRepository
{
    /**
     * PlayerRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function findSuccessfulPlayers($enigme, $team)
    {
        return $this->createQueryBuilder('p')
            ->join('p.playerEnigmas', 'pe')
            ->where('pe.enigma = :enigma')
            ->setParameter(':enigma', $enigme)
            ->andWhere('pe.solved = 3')
            ->andWhere('p.team = :team')
            ->setParameter(':team', $team)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByUniquePseudo(array $criteria)
    {
        return $this->findBy($criteria);
    }

    public function findByUniqueMail(array $criteria)
    {
        return $this->findBy($criteria);
    }

    // /**
    //  * @return Player[] Returns an array of Player objects
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
    public function findOneBySomeField($value): ?Player
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
