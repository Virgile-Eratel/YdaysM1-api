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

        // Créer des utilisateurs supplémentaires avec des noms réalistes
        $users = [
            ['email' => 'john.doe@example.com', 'role' => User::ROLE_USER, 'reference' => 'user-john'],
            ['email' => 'emma.smith@example.com', 'role' => User::ROLE_USER, 'reference' => 'user-emma'],
            ['email' => 'michael.brown@example.com', 'role' => User::ROLE_USER, 'reference' => 'user-michael'],
            ['email' => 'sophia.jones@example.com', 'role' => User::ROLE_USER, 'reference' => 'user-sophia'],
            ['email' => 'william.taylor@example.com', 'role' => User::ROLE_USER, 'reference' => 'user-william'],
            ['email' => 'olivia.wilson@example.com', 'role' => User::ROLE_USER, 'reference' => 'user-olivia'],
            ['email' => 'james.martin@example.com', 'role' => User::ROLE_USER, 'reference' => 'user-james'],
            ['email' => 'charlotte.davis@example.com', 'role' => User::ROLE_USER, 'reference' => 'user-charlotte'],
            ['email' => 'thomas.dupont@example.com', 'role' => User::ROLE_OWNER, 'reference' => 'user-thomas'],
            ['email' => 'sophie.martin@example.com', 'role' => User::ROLE_OWNER, 'reference' => 'user-sophie'],
            ['email' => 'lucas.bernard@example.com', 'role' => User::ROLE_OWNER, 'reference' => 'user-lucas'],
            ['email' => 'camille.petit@example.com', 'role' => User::ROLE_OWNER, 'reference' => 'user-camille'],
        ];

        foreach ($users as $userData) {
            $newUser = new User();
            $newUser->setEmail($userData['email']);
            $newUser->setRoles([$userData['role']]);
            $hashedPassword = $this->passwordHasher->hashPassword($newUser, 'password123');
            $newUser->setPassword($hashedPassword);
            $manager->persist($newUser);
            $this->addReference($userData['reference'], $newUser);
        }

        $manager->flush();
    }
}
