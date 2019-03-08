<?php

namespace App\Repository;

use App\Entity\RestaurantUserRating;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method RestaurantUserRating|null find($id, $lockMode = null, $lockVersion = null)
 * @method RestaurantUserRating|null findOneBy(array $criteria, array $orderBy = null)
 * @method RestaurantUserRating[]    findAll()
 * @method RestaurantUserRating[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RestaurantUserRatingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RestaurantUserRating::class);
    }

    // /**
    //  * @return RestaurantUserRating[] Returns an array of RestaurantUserRating objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RestaurantUserRating
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
