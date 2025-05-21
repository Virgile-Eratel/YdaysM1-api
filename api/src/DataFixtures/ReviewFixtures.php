<?php

namespace App\DataFixtures;

use App\Entity\Review;
use App\Entity\User;
use App\Entity\Place;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReviewFixtures extends Fixture implements DependentFixtureInterface
{
    // Messages positifs pour les notes élevées (4-5)
    private array $positiveMessages = [
        'Excellent espace de travail, très lumineux et calme. Je recommande vivement !',
        'Parfait pour une journée de télétravail, environnement calme et agréable.',
        'Accueil chaleureux et espace confortable. Parfait pour une journée de travail.',
        'Excellent rapport qualité-prix. Je reviendrai sans hésiter.',
        'Espace très bien aménagé et propre. Parfait pour travailler efficacement.',
        'Hôte très disponible et sympathique. L\'espace est conforme à la description.',
        'Très bonne expérience, l\'endroit est idéalement situé et bien équipé.',
        'Connexion internet rapide et stable. Parfait pour les visioconférences.',
        'Espace calme et confortable, idéal pour se concentrer.',
        'Super découverte ! L\'espace est magnifique et fonctionnel.',
        'Très bonne insonorisation, parfait pour les appels professionnels.',
        'Excellent pour des réunions d\'équipe, équipements de qualité.',
    ];

    // Messages neutres pour les notes moyennes (3)
    private array $neutralMessages = [
        'Bon rapport qualité-prix, mais un peu bruyant aux heures de pointe.',
        'Espace correct, mais manque un peu de confort pour de longues journées.',
        'Connexion internet instable par moments, mais l\'espace est agréable.',
        'Bien situé, mais manque d\'équipements pour travailler confortablement.',
        'Espace un peu petit mais fonctionnel. Hôte disponible.',
        'Bel espace, mais un peu cher pour ce qui est proposé.',
        'Correct dans l\'ensemble, mais la climatisation fait un peu de bruit.',
        'Espace agréable mais un peu sombre, manque de lumière naturelle.',
        'Propreté correcte, mais certains équipements mériteraient d\'être renouvelés.',
        'Bonne localisation, mais l\'isolation phonique pourrait être améliorée.',
    ];

    // Messages négatifs pour les notes basses (1-2)
    private array $negativeMessages = [
        'Déçu par la propreté des lieux. Dommage car bien situé.',
        'Trop bruyant pour travailler efficacement. Je ne reviendrai pas.',
        'Connexion internet très lente et instable. Impossible de faire des visioconférences.',
        'Espace beaucoup trop petit par rapport à ce qui était annoncé.',
        'Mauvais rapport qualité-prix. Je ne recommande pas.',
        'Hôte peu disponible et espace mal entretenu.',
        'Mobilier inconfortable, impossible de travailler plus de 2 heures.',
        'Problèmes de chauffage, j\'ai eu froid toute la journée.',
        'Trop de passage et de bruit, impossible de se concentrer.',
        'Espace mal ventilé et étouffant. Expérience désagréable.',
    ];

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
            $this->getReference('user-thomas', User::class),
            $this->getReference('user-sophie', User::class),
            $this->getReference('user-lucas', User::class),
            $this->getReference('user-camille', User::class),
        ];

        // Récupérer les places depuis PlaceFixtures
        $places = $manager->getRepository(Place::class)->findAll();

        // Pour chaque place, créer entre 1 et 8 reviews
        foreach ($places as $place) {
            $numReviews = rand(1, 8);
            $usedUsers = []; // Pour éviter qu'un même utilisateur laisse plusieurs avis sur le même lieu
            
            for ($i = 0; $i < $numReviews; $i++) {
                // Sélectionner un utilisateur aléatoire qui n'a pas encore laissé d'avis sur ce lieu
                do {
                    $userIndex = array_rand($users);
                    $author = $users[$userIndex];
                } while (in_array($userIndex, $usedUsers) && count($usedUsers) < count($users));
                
                $usedUsers[] = $userIndex;
                
                // Générer une note aléatoire (avec une tendance vers les notes positives)
                $ratingDistribution = [1, 2, 3, 3, 4, 4, 4, 5, 5, 5, 5]; // Distribution favorisant les notes positives
                $rating = $ratingDistribution[array_rand($ratingDistribution)];
                
                // Sélectionner un message approprié en fonction de la note
                if ($rating >= 4) {
                    $message = $this->positiveMessages[array_rand($this->positiveMessages)];
                } elseif ($rating == 3) {
                    $message = $this->neutralMessages[array_rand($this->neutralMessages)];
                } else {
                    $message = $this->negativeMessages[array_rand($this->negativeMessages)];
                }
                
                // Créer la review
                $review = new Review();
                $review->setMessage($message);
                $review->setRating($rating);
                $review->setAuthor($author);
                $review->setPlace($place);
                
                // Date de création aléatoire dans les 90 derniers jours
                $daysAgo = rand(1, 90);
                $review->setCreatedAt(new \DateTimeImmutable('-' . $daysAgo . ' days'));
                
                $manager->persist($review);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PlaceFixtures::class,
        ];
    }
}
