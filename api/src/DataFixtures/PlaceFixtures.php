<?php

namespace App\DataFixtures;

use App\Entity\Place;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PlaceFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var User $owner */
        $owner = $this->getReference('user-owner', User::class);

        $places = [
            [
                'title'       => 'Bureau moderne au centre de Rennes',
                'description' => 'Bureau moderne et lumineux situé en plein cœur de Rennes, idéal pour travailler.',
                'address'     => '20 Rue de Paris, 35000 Rennes',
                'latitude'    => 48.1173,
                'longitude'   => -1.6802,
                'price'       => 200,
                'image_name'  => 'office1.jpg',
            ],
            [
                'title'       => 'Espace coworking à Rennes',
                'description' => 'Espace de coworking chaleureux pour entrepreneurs et freelances, avec accès rapide aux transports.',
                'address'     => '30 Avenue de la Liberté, 35000 Rennes',
                'latitude'    => 48.1166,
                'longitude'   => -1.6780,
                'price'       => 180,
                'image_name'  => 'office3.jpg',
            ],
            [
                'title'       => 'Chambre chez l\'habitant à Rennes',
                'description' => 'Chambre confortable chez l\'habitant avec un espace bureau intégré pour travailler efficacement.',
                'address'     => '12 Rue de la République, 35000 Rennes',
                'latitude'    => 48.1175,
                'longitude'   => -1.6765,
                'price'       => 70,
                'image_name'  => 'office2.jpg',
            ],
            [
                'title'       => 'Bureau partagé en périphérie de Rennes',
                'description' => 'Bureau partagé dans un environnement calme en périphérie, idéal pour le télétravail.',
                'address'     => '15 Rue des Entreprises, 35200 Rennes',
                'latitude'    => 48.1100,
                'longitude'   => -1.6500,
                'price'       => 90,
                'image_name'  => 'office4.jpg',

            ],
            [
                'title'       => 'Bureau à Saint-Jacques-de-la-Lande',
                'description' => 'Espace bureau calme et confortable dans la commune de Saint-Jacques-de-la-Lande.',
                'address'     => '5 Rue des Affaires, 35500 Saint-Jacques-de-la-Lande',
                'latitude'    => 48.1024,
                'longitude'   => -1.7051,
                'price'       => 90,
                'image_name'  => 'office5.jpg',

            ],
            [
                'title'       => 'Bureau à Villejean - Espace de travail',
                'description' => 'Bureau bien équipé dans le quartier dynamique de Villejean, parfait pour travailler.',
                'address'     => '50 Rue du Centre, 35000 Rennes',
                'latitude'    => 48.1280,
                'longitude'   => -1.6650,
                'price'       => 95,
                'image_name'  => 'office6.jpg',

            ],
            [
                'title'       => 'Bureau privatif au cœur de Rennes',
                'description' => 'Bureau privatif offrant calme et confort pour une journée de travail productive.',
                'address'     => '22 Rue des Arts, 35000 Rennes',
                'latitude'    => 48.1180,
                'longitude'   => -1.6770,
                'price'       => 130,
                'image_name'  => 'office7.jpg',

            ],
            [
                'title'       => 'Cabine de travail pour télétravail',
                'description' => 'Petite cabine de télétravail équipée pour travailler efficacement dans un espace dédié.',
                'address'     => '8 Rue de la Paix, 35000 Rennes',
                'latitude'    => 48.1150,
                'longitude'   => -1.6780,
                'price'       => 50,
                'image_name'  => 'office8.jpg',

            ],
            [
                'title'       => 'Salle de réunion et travail collaboratif',
                'description' => 'Salle de réunion équipée pour les professionnels et favorisant le travail collaboratif.',
                'address'     => '45 Boulevard des Sciences, 35000 Rennes',
                'latitude'    => 48.1190,
                'longitude'   => -1.6820,
                'price'       => 150,
                'image_name'  => 'office9.jpg',

            ],
            [
                'title'       => 'Espace bureau innovant à Rennes Nord',
                'description' => 'Espace bureau moderne dans le secteur nord de Rennes, idéal pour start-ups et entrepreneurs.',
                'address'     => '32 Avenue des Technologies, 35000 Rennes',
                'latitude'    => 48.1300,
                'longitude'   => -1.6600,
                'price'       => 120,
            ],
            [
                'title'       => 'Appartement au centre de Rennes',
                'description' => 'Bel appartement lumineux au cœur de Rennes, proche des commerces et transports.',
                'address'     => '10 Place de la Mairie, 35000 Rennes',
                'latitude'    => 48.1116,
                'longitude'   => -1.6800,
                'price'       => 85,
                'image_name'  => 'appart1.jpg',
            ],
            [
                'title'       => 'Maison avec jardin à Cesson-Sévigné',
                'description' => 'Charmante maison avec jardin dans un quartier calme de Cesson-Sévigné.',
                'address'     => '5 Rue de la Rigourdiere, 35510 Cesson-Sévigné',
                'latitude'    => 48.1219,
                'longitude'   => -1.6042,
                'price'       => 120,
                'image_name'  => 'house1.jpg',
            ],
            [
                'title'       => 'Studio étudiant près de l\'université',
                'description' => 'Studio confortable idéal pour étudiant, à 5 minutes à pied du campus de Beaulieu.',
                'address'     => '15 Avenue du Professeur Charles Foulon, 35700 Rennes',
                'latitude'    => 48.1206,
                'longitude'   => -1.6394,
                'price'       => 65,
                'image_name'  => 'appart2.jpg',
            ],
            [
                'title'       => 'Loft moderne à Saint-Grégoire',
                'description' => 'Loft spacieux et moderne dans une résidence récente à Saint-Grégoire.',
                'address'     => '8 Rue Alphonse Milon, 35760 Saint-Grégoire',
                'latitude'    => 48.1539,
                'longitude'   => -1.6992,
                'price'       => 110,
            ],
            [
                'title'       => 'Maison de campagne à Bruz',
                'description' => 'Maison de campagne avec grand terrain, parfaite pour un séjour au calme à proximité de Rennes.',
                'address'     => '12 Rue du Temple, 35170 Bruz',
                'latitude'    => 48.0231,
                'longitude'   => -1.7444,
                'price'       => 150,
                'image_name'  => 'house2.jpg',
            ],
        ];

        foreach ($places as $placeData) {
            $place = new Place();
            $place->setTitle($placeData['title']);
            $place->setDescription($placeData['description']);
            $place->setAddress($placeData['address']);
            $place->setLatitude($placeData['latitude']);
            $place->setLongitude($placeData['longitude']);
            $place->setPrice($placeData['price']);
            $place->setHost($owner);
            $place->setCreatedAt(new \DateTimeImmutable());
            $place->setUpdatedAt(new \DateTimeImmutable());

            if (isset($placeData['image_name'])) {
                $place->setImageName($placeData['image_name']);
            }

            $manager->persist($place);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
