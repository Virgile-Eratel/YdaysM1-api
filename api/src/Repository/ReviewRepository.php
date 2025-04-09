<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\Place;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * Trouve toutes les reviews pour un lieu spécifique
     */
    public function findByPlace(Place $place): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.place = :place')
            ->setParameter('place', $place)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve toutes les reviews pour un lieu spécifique par son ID
     */
    public function findByPlaceId(int $placeId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.place = :placeId')
            ->setParameter('placeId', $placeId)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
