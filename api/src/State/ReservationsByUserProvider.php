<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\ReservationRepository;
use Symfony\Bundle\SecurityBundle\Security;

class ReservationsByUserProvider implements ProviderInterface
{
    private $reservationRepository;
    private $security;

    public function __construct(
        ReservationRepository $reservationRepository,
        Security $security
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->security = $security;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $userId = $uriVariables['userId'] ?? null;

        if (!$userId) {
            return [];
        }

        // Vérification des permissions
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            return [];
        }

        // Si l'utilisateur n'est pas admin et essaie d'accéder aux réservations d'un autre utilisateur
        if (!$this->security->isGranted('ROLE_ADMIN') && $currentUser->getId() != $userId) {
            return [];
        }

        return $this->reservationRepository->findByUser($userId);
    }
}
