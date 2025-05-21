<?php

namespace App\DataFixtures;

use App\Entity\Reservation;
use App\Entity\User;
use App\Entity\Place;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReservationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Récupérer tous les utilisateurs
        $users = [
            $this->getReference('user-regular', User::class),
            $this->getReference('user-owner', User::class),
            $this->getReference('user-john', User::class),
            $this->getReference('user-emma', User::class),
            $this->getReference('user-michael', User::class),
            $this->getReference('user-sophia', User::class),
            $this->getReference('user-william', User::class),
            $this->getReference('user-olivia', User::class),
            $this->getReference('user-james', User::class),
            $this->getReference('user-charlotte', User::class),
        ];

        // Récupérer les places depuis PlaceFixtures
        $places = $manager->getRepository(Place::class)->findAll();

        $now = new \DateTimeImmutable();
        
        // Statuts possibles pour les réservations
        $statuses = [
            Reservation::STATUS_PENDING,
            Reservation::STATUS_CONFIRMED,
            Reservation::STATUS_CANCELLED,
            Reservation::STATUS_COMPLETED
        ];
        
        // Distribution des statuts (favorisant les statuts confirmés et complétés)
        $statusDistribution = [
            Reservation::STATUS_PENDING => 20,    // 20%
            Reservation::STATUS_CONFIRMED => 40,  // 40%
            Reservation::STATUS_CANCELLED => 10,  // 10%
            Reservation::STATUS_COMPLETED => 30   // 30%
        ];
        
        // Créer entre 30 et 50 réservations
        $numReservations = rand(30, 50);
        
        for ($i = 0; $i < $numReservations; $i++) {
            $reservation = new Reservation();
            
            // Sélectionner un utilisateur aléatoire
            $user = $users[array_rand($users)];
            $reservation->setUser($user);
            
            // Sélectionner un lieu aléatoire
            $place = $places[array_rand($places)];
            $reservation->setPlace($place);
            
            // Sélectionner un statut en fonction de la distribution
            $status = $this->getRandomWeightedElement($statusDistribution);
            $reservation->setStatus($status);
            
            // Définir les dates en fonction du statut
            $startDate = null;
            $endDate = null;
            
            switch ($status) {
                case Reservation::STATUS_COMPLETED:
                    // Réservation terminée (dans le passé)
                    $startOffset = rand(30, 120); // Entre 1 et 4 mois dans le passé
                    $duration = rand(1, 7); // Entre 1 et 7 jours
                    $startDate = $now->modify('-' . $startOffset . ' days');
                    $endDate = $startDate->modify('+' . $duration . ' days');
                    break;
                    
                case Reservation::STATUS_CANCELLED:
                    // Réservation annulée (peut être dans le passé ou le futur)
                    if (rand(0, 1) === 0) {
                        // Annulée dans le passé
                        $startOffset = rand(10, 60);
                        $startDate = $now->modify('-' . $startOffset . ' days');
                    } else {
                        // Annulée pour une date future
                        $startOffset = rand(5, 60);
                        $startDate = $now->modify('+' . $startOffset . ' days');
                    }
                    $duration = rand(1, 5);
                    $endDate = $startDate->modify('+' . $duration . ' days');
                    break;
                    
                case Reservation::STATUS_CONFIRMED:
                    // Réservation confirmée (dans le futur)
                    $startOffset = rand(3, 60); // Entre 3 jours et 2 mois dans le futur
                    $duration = rand(1, 10); // Entre 1 et 10 jours
                    $startDate = $now->modify('+' . $startOffset . ' days');
                    $endDate = $startDate->modify('+' . $duration . ' days');
                    break;
                    
                case Reservation::STATUS_PENDING:
                default:
                    // Réservation en attente (dans le futur, généralement proche)
                    $startOffset = rand(1, 30); // Entre 1 et 30 jours dans le futur
                    $duration = rand(1, 5); // Entre 1 et 5 jours
                    $startDate = $now->modify('+' . $startOffset . ' days');
                    $endDate = $startDate->modify('+' . $duration . ' days');
                    break;
            }
            
            $reservation->setStartDate($startDate);
            $reservation->setEndDate($endDate);
            
            // Nombre de personnes (entre 1 et 5)
            $reservation->setNumberOfGuests(rand(1, 5));
            
            // Calculer le prix total
            $placePrice = $place->getPrice() ?? 0;
            $days = $startDate->diff($endDate)->days;
            $totalPrice = $placePrice * $days;
            $reservation->setTotalPrice($totalPrice);
            
            // Dates de création et mise à jour
            if ($status === Reservation::STATUS_COMPLETED || $status === Reservation::STATUS_CANCELLED) {
                // Pour les réservations terminées ou annulées, la date de création est plus ancienne
                $createdAt = $startDate->modify('-' . rand(5, 30) . ' days');
                $updatedAt = $createdAt->modify('+' . rand(1, 5) . ' days');
            } else {
                // Pour les réservations en attente ou confirmées, la date de création est récente
                $createdAt = $now->modify('-' . rand(1, 10) . ' days');
                $updatedAt = $createdAt->modify('+' . rand(0, 3) . ' days');
            }
            
            $reservation->setCreatedAt($createdAt);
            $reservation->setUpdatedAt($updatedAt);
            
            $manager->persist($reservation);
        }

        $manager->flush();
    }
    
    /**
     * Sélectionne un élément aléatoire en fonction de poids
     * 
     * @param array $weightedValues Tableau associatif où les clés sont les valeurs et les valeurs sont les poids
     * @return mixed La clé sélectionnée
     */
    private function getRandomWeightedElement(array $weightedValues)
    {
        $rand = rand(1, array_sum($weightedValues));
        
        foreach ($weightedValues as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $key;
            }
        }
        
        return array_key_first($weightedValues); // Fallback
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PlaceFixtures::class,
        ];
    }
}
