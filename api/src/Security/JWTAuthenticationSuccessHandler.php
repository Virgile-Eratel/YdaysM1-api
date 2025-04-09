<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler as BaseAuthenticationSuccessHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class JWTAuthenticationSuccessHandler extends BaseAuthenticationSuccessHandler
{
    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        EventDispatcherInterface $dispatcher,
        array $cookieProviders = []
    ) {
        parent::__construct($jwtManager, $dispatcher, $cookieProviders);
    }

    /**
     * @param TokenInterface $token
     * @param Request $request
     * @return JsonResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        // Générer la réponse de base avec le token JWT
        $response = parent::onAuthenticationSuccess($request, $token);

        // Récupérer les données de la réponse
        $data = json_decode($response->getContent(), true);

        /** @var UserInterface $user */
        $user = $token->getUser();

        // Vérifier si l'utilisateur a une méthode getId()
        if (method_exists($user, 'getId')) {
            // Ajouter l'ID de l'utilisateur à la réponse
            $data['userId'] = $user->getId();
        }

        // Déterminer le rôle principal (owner ou user)
        $userRole = 'user'; // Par défaut

        if (in_array('ROLE_OWNER', $user->getRoles())) {
            $userRole = 'owner';
        }

        // Ajouter le rôle à la réponse
        $data['role'] = $userRole;

        // Mettre à jour la réponse
        $response->setData($data);

        return $response;
    }
}
