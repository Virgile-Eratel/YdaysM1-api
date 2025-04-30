<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 *
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Trouve toutes les réservations d'un utilisateur
     */
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('r.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve toutes les réservations pour une place
     */
    public function findByPlace(int $placeId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.place = :placeId')
            ->setParameter('placeId', $placeId)
            ->orderBy('r.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si une place est disponible pour une période donnée
     */
    public function isPlaceAvailable(int $placeId, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): bool
    {
        $overlappingReservations = $this->createQueryBuilder('r')
            ->andWhere('r.place = :placeId')
            ->andWhere('r.status != :cancelledStatus')
            ->andWhere(
                '(r.startDate <= :endDate AND r.endDate >= :startDate)'
            )
            ->setParameter('placeId', $placeId)
            ->setParameter('cancelledStatus', Reservation::STATUS_CANCELLED)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();

        return count($overlappingReservations) === 0;
    }

    /**
     * Trouve toutes les réservations pour les places d'un propriétaire
     */
    public function findByOwner(int $ownerId): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.place', 'p')
            ->andWhere('p.host = :ownerId')
            ->setParameter('ownerId', $ownerId)
            ->orderBy('r.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
