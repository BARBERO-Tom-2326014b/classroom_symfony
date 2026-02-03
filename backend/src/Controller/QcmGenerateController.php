<?php

namespace App\Controller;

use App\Entity\Document;
use App\Factory\QcmFactory;
use App\Repository\DocumentRepository;
use App\Service\MistralClient;
use App\Service\PdfTextExtractor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class QcmGenerateController extends AbstractController
{
    #[Route('/qcm/generate', name: 'qcm_generate', methods: ['POST'])]
    public function generate(
        Request $request,
        DocumentRepository $documentRepository,
        PdfTextExtractor $pdfTextExtractor,
        MistralClient $mistralClient,
        QcmFactory $qcmFactory,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_PROFESSEUR');

        if (!$this->isCsrfTokenValid('qcm_generate', (string) $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('home');
        }

        $documentId = $request->request->get('document_id');
        if ($documentId === null || !ctype_digit((string) $documentId)) {
            $this->addFlash('error', 'Veuillez sélectionner un document PDF.');
            return $this->redirectToRoute('home');
        }

        /** @var Document|null $document */
        $document = $documentRepository->find((int) $documentId);
        if (!$document || !$document->getPdfName()) {
            $this->addFlash('error', 'Document introuvable ou sans PDF.');
            return $this->redirectToRoute('home');
        }

        $pdfPath = $this->getParameter('kernel.project_dir') . '/public/uploads/documents/' . $document->getPdfName();
        $text = $pdfTextExtractor->extract($pdfPath);

        if (trim($text) === '') {
            $this->addFlash('error', 'PDF vide ou illisible.');
            return $this->redirectToRoute('home');
        }

        try {
            $qcmData = $mistralClient->generateQcm($text);
            $qcm = $qcmFactory->createFromAiResponse($qcmData, $document);

            $em->persist($qcm);
            $em->flush();

            $this->addFlash('success', sprintf('QCM généré avec succès (id=%d).', (int) $qcm->getId()));
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Erreur lors de la génération du QCM : ' . $e->getMessage());
        }

        return $this->redirectToRoute('home');
    }
}
