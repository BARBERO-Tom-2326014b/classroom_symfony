<?php

namespace App\Controller\Api;

use App\Repository\QcmRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class QcmController extends AbstractController
{
    #[Route('/qcms', methods: ['GET'])]
    public function list(QcmRepository $repo): JsonResponse
    {
        return $this->json(
            $repo->findAll(),
            200,
            [],
            ['groups' => 'qcm:list']
        );
    }
}
