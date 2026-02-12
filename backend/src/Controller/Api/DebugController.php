<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class DebugController extends AbstractController
{
    #[Route('/api/debug/check-credentials', name: 'api_debug_credentials', methods: ['POST'])]
    public function checkCredentials(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé',
                'email' => $email,
            ]);
        }

        $isPasswordValid = $passwordHasher->isPasswordValid($user, $password);

        return $this->json([
            'success' => $isPasswordValid,
            'message' => $isPasswordValid ? 'Identifiants valides' : 'Mot de passe incorrect',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ],
        ]);
    }

    #[Route('/api/debug/session', name: 'api_debug_session', methods: ['GET'])]
    public function debugSession(Request $request): JsonResponse
    {
        $session = $request->getSession();

        return $this->json([
            'session_id' => $session->getId(),
            'session_started' => $session->isStarted(),
            'session_attributes' => $session->all(),
            'cookies' => $request->cookies->all(),
            'user' => $this->getUser() ? [
                'email' => $this->getUser()->getUserIdentifier(),
                'roles' => method_exists($this->getUser(), 'getRoles') ? $this->getUser()->getRoles() : [],
            ] : null,
        ]);
    }
}
