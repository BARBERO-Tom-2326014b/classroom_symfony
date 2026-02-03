<?php

namespace App\Controller;

use App\Entity\Qcm;
use App\Entity\User;
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

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $qcms = $qcmRepository->findBy(['author' => $user], ['id' => 'DESC']);

        return $this->render('qcm/index.html.twig', [
            'qcms' => $qcms,
        ]);
    }

    #[Route('/qcms/{id}', name: 'qcm_show', methods: ['GET'])]
    public function show(Qcm $qcm): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PROFESSEUR');

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if ($qcm->getAuthor()?->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('qcm/show.html.twig', [
            'qcm' => $qcm,
        ]);
    }
}
