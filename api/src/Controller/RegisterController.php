<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController
{
    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
    }

    #[Route('/api/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Email and password are required'], 400);
        }

        // Vérifier que l'email n'existe pas déjà en base
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'Email already in use'], 400);
        }

        $user = new User();
        $user->setEmail($data['email']);

        // Définir les rôles en s'assurant qu'un utilisateur ne peut pas s'ajouter `ROLE_ADMIN`
        $validRoles = [User::ROLE_CLIENT, User::ROLE_OWNER]; // Rôles autorisés à l'inscription
        $roles = isset($data['roles']) && is_array($data['roles']) ? array_intersect($data['roles'], $validRoles) : [];

        if (empty($roles)) {
            $roles = [User::ROLE_CLIENT];
        }

        $user->setRoles($roles);

        // Encoder le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        // Sauvegarde en base
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'User created successfully',
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ], 201);
    }
}
