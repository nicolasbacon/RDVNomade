<?php

namespace App\Repository;

use App\Entity\PlayerEnigma;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PlayerEnigma|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerEnigma|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerEnigma[]    findAll()
 * @method PlayerEnigma[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerEnigmaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerEnigma::class);
    }

    // /**
    //  * @return PlayerEnigma[] Returns an array of PlayerEnigma objects
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


    public function findPlayerEnigmaAndEnigmaByPlayer($player)
    {
        return $this->createQueryBuilder('pe')
            ->leftJoin('pe.enigma', 'enigma')
            ->addSelect('enigma')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findEnigmasByPlayer($player) : array
    {
        /*return $this->createQueryBuilder('pe')
            ->addSelect('enigma.id, enigma.name, enigma.description, enigma.answer, enigma.star')
            ->leftJoin('pe.enigma', 'enigma')
            ->addSelect('enigma')
            ->getQuery()
            ->getResult()
            ;*/
        return $this->createQueryBuilder('pe')
            ->select('enigma.id, enigma.name, enigma.description, enigma.answer, enigma.star')
            ->from('player_enigma', 'pe')
            ->join('pe.enigma', 'enigma')
            ->where('pe.player = :player')
            ->setParameter(':player', $player)
            ->getQuery()
            ->getResult()
            ;
    }

}
