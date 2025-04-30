<?php

namespace App\Controller;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class ReservationController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    #[Route('/api/reservations/{id}/confirm-payment', name: 'app_reservation_confirm_payment', methods: ['POST'])]
    public function confirmPayment(Request $request, int $id): JsonResponse
    {
        $reservation = $this->entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            return new JsonResponse(['error' => 'Reservation not found'], 404);
        }

        // Vérifier que l'utilisateur est autorisé à confirmer le paiement
        $currentUser = $this->security->getUser();
        if (!$currentUser || ($currentUser !== $reservation->getUser() && !$this->security->isGranted('ROLE_ADMIN'))) {
            throw new AccessDeniedException('You are not allowed to confirm this payment');
        }

        // Récupérer les données de la requête
        $data = json_decode($request->getContent(), true);
        $stripePaymentId = $data['stripePaymentId'] ?? null;

        if (!$stripePaymentId) {
            return new JsonResponse(['error' => 'Stripe payment ID is required'], 400);
        }

        // Mettre à jour le statut de la réservation
        $reservation->setStatus(Reservation::STATUS_CONFIRMED);
        $reservation->setStripePaymentId($stripePaymentId);

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Payment confirmed successfully',
            'reservation' => [
                'id' => $reservation->getId(),
                'status' => $reservation->getStatus(),
                'stripePaymentId' => $reservation->getStripePaymentId()
            ]
        ]);
    }

    #[Route('/api/reservations/{id}/cancel', name: 'app_reservation_cancel', methods: ['POST'])]
    public function cancelReservation(int $id): JsonResponse
    {
        $reservation = $this->entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            return new JsonResponse(['error' => 'Reservation not found'], 404);
        }

        // Vérifier que l'utilisateur est autorisé à annuler la réservation
        $currentUser = $this->security->getUser();
        if (!$currentUser || ($currentUser !== $reservation->getUser() && !$this->security->isGranted('ROLE_ADMIN'))) {
            throw new AccessDeniedException('You are not allowed to cancel this reservation');
        }

        // Mettre à jour le statut de la réservation
        $reservation->setStatus(Reservation::STATUS_CANCELLED);

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Reservation cancelled successfully',
            'reservation' => [
                'id' => $reservation->getId(),
                'status' => $reservation->getStatus()
            ]
        ]);
    }

    #[Route('/api/reservations/{id}/complete', name: 'app_reservation_complete', methods: ['POST'])]
    public function completeReservation(int $id): JsonResponse
    {
        $reservation = $this->entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            return new JsonResponse(['error' => 'Reservation not found'], 404);
        }

        // Vérifier que l'utilisateur est autorisé à marquer la réservation comme terminée
        $currentUser = $this->security->getUser();
        $isOwner = $currentUser === $reservation->getPlace()->getHost();

        if (!$currentUser || (!$isOwner && !$this->security->isGranted('ROLE_ADMIN'))) {
            throw new AccessDeniedException('You are not allowed to complete this reservation');
        }

        // Vérifier que la réservation est confirmée
        if ($reservation->getStatus() !== Reservation::STATUS_CONFIRMED) {
            return new JsonResponse(['error' => 'Only confirmed reservations can be completed'], 400);
        }

        // Mettre à jour le statut de la réservation
        $reservation->setStatus(Reservation::STATUS_COMPLETED);

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Reservation completed successfully',
            'reservation' => [
                'id' => $reservation->getId(),
                'status' => $reservation->getStatus()
            ]
        ]);
    }

    #[Route('/api/reservations/{id}/confirm', name: 'app_reservation_confirm', methods: ['POST'])]
    public function confirmReservation(int $id): JsonResponse
    {
        $reservation = $this->entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            return new JsonResponse(['error' => 'Reservation not found'], 404);
        }

        // Vérifier que l'utilisateur est autorisé à confirmer la réservation
        $currentUser = $this->security->getUser();
        $isOwner = $currentUser === $reservation->getPlace()->getHost();

        if (!$currentUser || (!$isOwner && !$this->security->isGranted('ROLE_ADMIN'))) {
            throw new AccessDeniedException('You are not allowed to confirm this reservation');
        }

        // Vérifier que la réservation est en attente
        if ($reservation->getStatus() !== Reservation::STATUS_PENDING) {
            return new JsonResponse(['error' => 'Only pending reservations can be confirmed'], 400);
        }

        // Mettre à jour le statut de la réservation
        $reservation->setStatus(Reservation::STATUS_CONFIRMED);

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Reservation confirmed successfully',
            'reservation' => [
                'id' => $reservation->getId(),
                'status' => $reservation->getStatus()
            ]
        ]);
    }

    #[Route('/api/reservations/{id}/owner-cancel', name: 'app_reservation_owner_cancel', methods: ['POST'])]
    public function ownerCancelReservation(int $id): JsonResponse
    {
        $reservation = $this->entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            return new JsonResponse(['error' => 'Reservation not found'], 404);
        }

        // Vérifier que l'utilisateur est autorisé à annuler la réservation
        $currentUser = $this->security->getUser();
        $isOwner = $currentUser === $reservation->getPlace()->getHost();

        if (!$currentUser || (!$isOwner && !$this->security->isGranted('ROLE_ADMIN'))) {
            throw new AccessDeniedException('You are not allowed to cancel this reservation');
        }

        // Vérifier que la réservation peut être annulée
        if (!in_array($reservation->getStatus(), [Reservation::STATUS_PENDING, Reservation::STATUS_CONFIRMED])) {
            return new JsonResponse(['error' => 'This reservation cannot be cancelled'], 400);
        }

        // Mettre à jour le statut de la réservation
        $reservation->setStatus(Reservation::STATUS_CANCELLED);

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Reservation cancelled successfully',
            'reservation' => [
                'id' => $reservation->getId(),
                'status' => $reservation->getStatus()
            ]
        ]);
    }

    #[Route('/api/owner/reservations', name: 'app_owner_reservations', methods: ['GET'])]
    public function getOwnerReservations(): JsonResponse
    {
        // Vérifier que l'utilisateur est un propriétaire
        $currentUser = $this->security->getUser();
        if (!$currentUser || !$this->security->isGranted('ROLE_OWNER')) {
            throw new AccessDeniedException('You must be an owner to access this resource');
        }

        // Récupérer les réservations des logements du propriétaire
        $reservations = $this->entityManager->getRepository(Reservation::class)->findByOwner($currentUser->getId());

        // Formater les données pour la réponse
        $formattedReservations = [];
        foreach ($reservations as $reservation) {
            $place = $reservation->getPlace();
            $user = $reservation->getUser();

            $formattedReservations[] = [
                'id' => $reservation->getId(),
                'startDate' => $reservation->getStartDate()->format('d/m/Y'),
                'endDate' => $reservation->getEndDate()->format('d/m/Y'),
                'numberOfGuests' => $reservation->getNumberOfGuests(),
                'totalPrice' => $reservation->getTotalPrice(),
                'status' => $reservation->getStatus(),
                'createdAt' => $reservation->getCreatedAt()->format('d/m/Y H:i'),
                'place' => [
                    'id' => $place->getId(),
                    'title' => $place->getTitle(),
                    'address' => $place->getAddress(),
                    'price' => $place->getPrice()
                ],
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail()
                ]
            ];
        }

        // Statistiques
        $pending = count(array_filter($reservations, fn($r) => $r->getStatus() === Reservation::STATUS_PENDING));
        $confirmed = count(array_filter($reservations, fn($r) => $r->getStatus() === Reservation::STATUS_CONFIRMED));
        $completed = count(array_filter($reservations, fn($r) => $r->getStatus() === Reservation::STATUS_COMPLETED));
        $cancelled = count(array_filter($reservations, fn($r) => $r->getStatus() === Reservation::STATUS_CANCELLED));
        $totalRevenue = array_reduce(
            array_filter($reservations, fn($r) => in_array($r->getStatus(), [Reservation::STATUS_CONFIRMED, Reservation::STATUS_COMPLETED])),
            fn($sum, $r) => $sum + $r->getTotalPrice(),
            0
        );

        return new JsonResponse([
            'reservations' => $formattedReservations,
            'stats' => [
                'total' => count($reservations),
                'pending' => $pending,
                'confirmed' => $confirmed,
                'completed' => $completed,
                'cancelled' => $cancelled,
                'totalRevenue' => $totalRevenue
            ]
        ]);
    }
}
