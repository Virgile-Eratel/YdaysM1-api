<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController
{
    private $em;
    private $reservationRepository;
    private $urlGenerator;

    public function __construct(
        EntityManagerInterface $em,
        ReservationRepository $reservationRepository,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->em = $em;
        $this->reservationRepository = $reservationRepository;
        $this->urlGenerator = $urlGenerator;
    }

    #[Route('/api/create-checkout-session/{id}', name: 'create_checkout_session', methods: ['POST'])]
    public function createCheckoutSession(Request $request, int $id): Response
    {
        $reservation = $this->reservationRepository->find($id);

        if (!$reservation) {
            return new JsonResponse(['error' => 'Réservation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'utilisateur actuel est bien le propriétaire de la réservation
        $currentUser = $this->getUser();
        if (!$currentUser || $reservation->getUser()->getId() !== $currentUser->getId()) {
            return new JsonResponse(['error' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        // Vérifier que la réservation est en statut "pending"
        if ($reservation->getStatus() !== Reservation::STATUS_PENDING) {
            return new JsonResponse(['error' => 'Cette réservation ne peut pas être payée'], Response::HTTP_BAD_REQUEST);
        }

        // Initialiser Stripe avec la clé secrète
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        // Créer une session de paiement Stripe
        try {
            // Utiliser directement les URLs du frontend pour les redirections
            $frontendUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:5173';

            $checkoutSession = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Réservation de ' . $reservation->getPlace()->getTitle(),
                            'description' => 'Du ' . $reservation->getStartDate()->format('d/m/Y') . ' au ' . $reservation->getEndDate()->format('d/m/Y'),
                        ],
                        'unit_amount' => (int)($reservation->getTotalPrice() * 100), // Montant en centimes
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $frontendUrl . '/reservations?payment_success=true',
                'cancel_url' => $frontendUrl . '/reservations?payment_canceled=true',
                'client_reference_id' => $reservation->getId(),
            ]);

            // Mettre à jour la réservation avec l'ID de la session Stripe
            $reservation->setStripePaymentId($checkoutSession->id);
            $this->em->flush();

            return new JsonResponse(['id' => $checkoutSession->id]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Cette route n'est plus utilisée directement par Stripe, mais nous la gardons pour la compatibilité
    // et pour permettre de mettre à jour le statut des réservations si nécessaire
    #[Route('/api/payment-success/{id}', name: 'payment_success', methods: ['GET'])]
    public function paymentSuccess(int $id): Response
    {
        $reservation = $this->reservationRepository->find($id);

        if (!$reservation) {
            return new JsonResponse(['error' => 'Réservation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier le statut du paiement avec Stripe
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        try {
            $session = Session::retrieve($reservation->getStripePaymentId());

            if ($session->payment_status === 'paid') {
                // Mettre à jour le statut de la réservation
                $reservation->setStatus(Reservation::STATUS_CONFIRMED);
                $this->em->flush();
            }
        } catch (\Exception $e) {
            // En cas d'erreur, on ne fait rien et on laisse la réservation en statut "pending"
        }

        // Rediriger vers la page des réservations
        $frontendUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:5173';
        return $this->redirect($frontendUrl . '/reservations');
    }

    #[Route('/api/payment-cancel/{id}', name: 'payment_cancel', methods: ['GET'])]
    public function paymentCancel(int $id): Response
    {
        // Rediriger vers la page des réservations
        $frontendUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:5173';
        return $this->redirect($frontendUrl . '/reservations?payment_canceled=true');
    }

    #[Route('/api/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function stripeWebhook(Request $request): Response
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        $endpoint_secret = $_ENV['STRIPE_WEBHOOK_SECRET'];

        $payload = $request->getContent();
        $sig_header = $request->headers->get('stripe-signature');
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            return new Response('Invalid payload', 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            return new Response('Invalid signature', 400);
        }

        // Gérer l'événement
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $this->handleCheckoutSessionCompleted($session);
                break;
            default:
                // Événement non géré
                break;
        }

        return new Response('Webhook handled', 200);
    }

    private function handleCheckoutSessionCompleted($session)
    {
        // Récupérer la réservation associée à cette session
        $reservationId = $session->client_reference_id;
        $reservation = $this->reservationRepository->find($reservationId);

        if (!$reservation) {
            return;
        }

        // Mettre à jour le statut de la réservation
        $reservation->setStatus(Reservation::STATUS_CONFIRMED);
        $this->em->flush();
    }
}
