<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class AuthController extends AbstractController
{
    #[Route('/api/csrf-token', name: 'api_csrf_token', methods: ['GET'])]
    public function csrfToken(CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        return $this->json([
            'token' => $csrfTokenManager->getToken('authenticate')->getValue(),
        ]);
    }

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        // Évite de sérialiser toute l’entité User (et potentiels cycles)
        return $this->json([
            'id' => method_exists($user, 'getId') ? $user->getId() : null,
            'email' => method_exists($user, 'getEmail') ? $user->getEmail() : $user->getUserIdentifier(),
            'roles' => method_exists($user, 'getRoles') ? $user->getRoles() : [],
        ]);
    }
}
