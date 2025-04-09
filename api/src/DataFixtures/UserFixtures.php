<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Créer un utilisateur avec le rôle OWNER
        $owner = new User();
        $owner->setEmail('owner@example.com');
        $owner->setRoles([User::ROLE_OWNER]);
        $hashedPassword = $this->passwordHasher->hashPassword($owner, 'password123');
        $owner->setPassword($hashedPassword);
        $manager->persist($owner);
        $this->addReference('user-owner', $owner);

        // Créer un utilisateur avec le rôle USER
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setRoles([User::ROLE_USER]);
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);
        $manager->persist($user);
        $this->addReference('user-regular', $user);

        $manager->flush();
    }
}
