<?php

namespace App\Controller;

use App\Entity\Qcm;
use App\Repository\QcmRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class QcmViewController extends AbstractController
{
    #[Route('/qcms', name: 'qcm_index', methods: ['GET'])]
    public function index(QcmRepository $qcmRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PROFESSEUR');

        $qcms = $qcmRepository->findBy([], ['id' => 'DESC']);

        return $this->render('qcm/index.html.twig', [
            'qcms' => $qcms,
        ]);
    }

    #[Route('/qcms/{id}', name: 'qcm_show', methods: ['GET'])]
    public function show(Qcm $qcm): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PROFESSEUR');

        return $this->render('qcm/show.html.twig', [
            'qcm' => $qcm,
        ]);
    }
}
