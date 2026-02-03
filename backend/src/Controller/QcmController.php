<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Qcm;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Factory\QcmFactory;
use App\Repository\DocumentRepository;
use App\Repository\QcmRepository;
use App\Repository\QuestionRepository;
use App\Service\MistralClient;
use App\Service\MistralQcmGenerator;
use App\Service\PdfTextExtractor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class QcmController extends AbstractController
{

    #[Route('/documents/{id}/generate-qcm', name: 'generate_qcm', methods: ['POST'])]
    public function generateQcm(
        Document $document,
        PdfTextExtractor $pdfTextExtractor,
        MistralClient $mistralClient,
        QcmFactory $qcmFactory,
        EntityManagerInterface $em
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_PROFESSEUR');

        // 1️⃣ extraire le texte du PDF
        $pdfPath = $this->getParameter('kernel.project_dir')
            . '/public/uploads/documents/' . $document->getPdfName();

        $text = $pdfTextExtractor->extract($pdfPath);

        if (!$text) {
            return $this->json(['error' => 'PDF vide ou illisible'], 400);
        }

        // 2️⃣ appel API Mistral
        $qcmData = $mistralClient->generateQcm($text);

        // 3️⃣ création entités QCM / Questions / Réponses
        $qcm = $qcmFactory->createFromAiResponse($qcmData, $document);

        $em->persist($qcm);
        $em->flush();

        return $this->json([
            'status' => 'ok',
            'qcm_id' => $qcm->getId()
        ]);
    }

    // ✅ CRÉER UN QCM
    #[Route('/qcms', methods: ['POST'])]
    public function createQcm(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $qcm = new Qcm();
        $qcm->setTitle($data['title']);

        $em->persist($qcm);
        $em->flush();

        return $this->json($qcm, 201, [], ['groups' => 'qcm:read']);
    }

    // ✅ AJOUTER UNE QUESTION À UN QCM
    #[Route('/questions', methods: ['POST'])]
    public function createQuestion(
        Request $request,
        EntityManagerInterface $em,
        QcmRepository $qcmRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $qcm = $qcmRepository->find($data['qcmId']);
        if (!$qcm) {
            return $this->json(['error' => 'QCM introuvable'], 404);
        }

        $question = new Question();
        $question->setLabel($data['label']);
        $question->setQcm($qcm);

        $em->persist($question);
        $em->flush();

        return $this->json($question, 201, [], ['groups' => 'qcm:read']);
    }

    // ✅ AJOUTER UNE RÉPONSE À UNE QUESTION
    #[Route('/reponses', methods: ['POST'])]
    public function createReponse(
        Request $request,
        EntityManagerInterface $em,
        QuestionRepository $questionRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $question = $questionRepository->find($data['questionId']);
        if (!$question) {
            return $this->json(['error' => 'Question introuvable'], 404);
        }

        $reponse = new Reponse();
        $reponse->setLabel($data['label']);
        $reponse->setIsCorrect($data['isCorrect']);
        $reponse->setQuestion($question);

        $em->persist($reponse);
        $em->flush();

        return $this->json($reponse, 201, [], ['groups' => 'qcm:read']);
    }

    #[Route('/qcms/{id}/json', name: 'api_qcm_json', methods: ['GET'])]
    public function qcmJson(Qcm $qcm): JsonResponse
    {
        // Optionnel: restreindre l'accès si besoin
        // $this->denyAccessUnlessGranted('ROLE_PROFESSEUR');

        return $this->json($qcm, 200, [], ['groups' => 'qcm:read']);
    }


}
