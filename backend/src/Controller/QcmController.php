<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Qcm;
use App\Entity\QcmAttempt;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Entity\User;
use App\Factory\QcmFactory;
use App\Repository\QcmAttemptRepository;
use App\Repository\QcmRepository;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use App\Service\MistralClient;
use App\Service\PdfTextExtractor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class QcmController extends AbstractController
{
    #[Route('/documents/{id}/generate-qcm', name: 'generate_qcm', methods: ['POST'])]
    public function generateQcm(
        Document $document,
        PdfTextExtractor $pdfTextExtractor,
        MistralClient $mistralClient,
        QcmFactory $qcmFactory,
        EntityManagerInterface $em,
        Request $request
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_PROFESSEUR');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Utilisateur invalide'], Response::HTTP_UNAUTHORIZED);
        }

        $payload = json_decode($request->getContent() ?: '[]', true);
        $questionCount = (int) ($payload['questionCount'] ?? 5);
        $questionCount = max(2, min(20, $questionCount));
        $allowBoolean = (bool) ($payload['allowBoolean'] ?? true);

        // 1️⃣ extraire le texte du PDF
        $pdfPath = $this->getParameter('kernel.project_dir')
            . '/public/uploads/documents/' . $document->getPdfName();

        $text = $pdfTextExtractor->extract($pdfPath);

        if (!$text) {
            return $this->json(['error' => 'PDF vide ou illisible'], 400);
        }

        // 2️⃣ appel API Mistral
        $qcmData = $mistralClient->generateQcm($text, $questionCount, $allowBoolean);

        // 3️⃣ création entités QCM / Questions / Réponses
        $qcm = $qcmFactory->createFromAiResponse($qcmData, $document);
        $qcm->setAuthor($user);

        $em->persist($qcm);
        $em->flush();

        return $this->json([
            'status' => 'ok',
            'qcm_id' => $qcm->getId()
        ]);
    }

    // ✅ CRÉER UN QCM
    #[Route('/qcms/new', name: 'api_qcm_create', methods: ['POST'])]
    public function createQcm(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_PROFESSEUR');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Utilisateur invalide'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        $qcm = new Qcm();
        $qcm->setTitle($data['title']);
        $qcm->setAuthor($user);

        $em->persist($qcm);
        $em->flush();

        return $this->json($qcm, 201, [], ['groups' => 'qcm:read']);
    }

    // ✅ LISTE des QCM du prof connecté
    #[Route('/qcms', name: 'api_qcm_list', methods: ['GET'])]
    public function listQcms(QcmRepository $qcmRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_PROFESSEUR');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Utilisateur invalide'], Response::HTTP_UNAUTHORIZED);
        }

        $qcms = $qcmRepository->findBy(['author' => $user], ['id' => 'DESC']);

        return $this->json($qcms, 200, [], ['groups' => 'qcm:list']);
    }

    // ✅ LISTE des QCM disponibles pour les étudiants
    #[Route('/qcms/available', name: 'api_qcm_available', methods: ['GET'])]
    public function availableQcms(QcmRepository $qcmRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ETUDIANT');

        // Pour l'instant: tous les QCM (on pourra filtrer sur published plus tard)
        $qcms = $qcmRepository->findBy([], ['id' => 'DESC']);

        return $this->json($qcms, 200, [], ['groups' => 'qcm:list']);
    }

    // ✅ AJOUTER UNE QUESTION À UN QCM
    #[Route('/questions', name: 'api_question_create', methods: ['POST'])]
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
    #[Route('/reponses', name: 'api_reponse_create', methods: ['POST'])]
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
        $this->denyAccessUnlessGranted('ROLE_PROFESSEUR');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Utilisateur invalide'], Response::HTTP_UNAUTHORIZED);
        }

        if ($qcm->getAuthor()?->getId() !== $user->getId()) {
            return $this->json(['error' => 'Accès interdit'], Response::HTTP_FORBIDDEN);
        }

        return $this->json($qcm, 200, [], ['groups' => 'qcm:read']);
    }

    #[Route('/qcms/{id}', name: 'api_qcm_show', methods: ['GET'])]
    public function showQcm(Qcm $qcm): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ETUDIANT');

        return $this->json($qcm, 200, [], ['groups' => 'qcm:read']);
    }

    #[Route('/my/qcm-attempts', name: 'api_my_qcm_attempts', methods: ['GET'])]
    public function myAttempts(QcmAttemptRepository $attemptRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ETUDIANT');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Utilisateur invalide'], Response::HTTP_UNAUTHORIZED);
        }

        $attempts = $attemptRepository->findBy(['user' => $user], ['submittedAt' => 'DESC']);

        // On renvoie juste l'info minimale utile au front
        $payload = array_map(static function (QcmAttempt $a) {
            return [
                'id' => $a->getId(),
                'qcmId' => $a->getQcm()?->getId(),
                'score' => $a->getScore(),
                'total' => $a->getTotal(),
                'submittedAt' => $a->getSubmittedAt()->format(DATE_ATOM),
            ];
        }, $attempts);

        return $this->json($payload);
    }

    #[Route('/qcms/{id}/submit', name: 'api_qcm_submit', methods: ['POST'])]
    public function submitQcm(
        Qcm $qcm,
        Request $request,
        ReponseRepository $reponseRepository,
        QcmAttemptRepository $attemptRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ETUDIANT');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Utilisateur invalide'], Response::HTTP_UNAUTHORIZED);
        }

        // ✅ Interdiction de refaire le QCM
        $existing = $attemptRepository->findOneBy(['user' => $user, 'qcm' => $qcm]);
        if ($existing) {
            return $this->json([
                'error' => 'QCM déjà réalisé.',
                'attemptId' => $existing->getId(),
                'score' => $existing->getScore(),
                'total' => $existing->getTotal(),
            ], 409);
        }

        /**
         * Payload attendu:
         * {
         *   "answers": {
         *     "<questionId>": <reponseId>
         *   }
         * }
         */
        $data = json_decode($request->getContent(), true);
        $answers = $data['answers'] ?? null;
        if (!is_array($answers)) {
            return $this->json(['error' => 'Payload invalide (answers attendu).'], 400);
        }

        $total = $qcm->getQuestions()->count();
        $score = 0;
        $correction = [];

        foreach ($qcm->getQuestions() as $question) {
            $qid = (string) $question->getId();

            $selectedRep = null;
            if (array_key_exists($qid, $answers)) {
                $repId = (int) $answers[$qid];
                $rep = $reponseRepository->find($repId);
                if ($rep && $rep->getQuestion()->getId() === $question->getId()) {
                    $selectedRep = $rep;
                }
            }

            $correctRep = null;
            foreach ($question->getReponses() as $r) {
                if ($r->isCorrect()) {
                    $correctRep = $r;
                    break;
                }
            }

            $isCorrect = $selectedRep && $correctRep && $selectedRep->getId() === $correctRep->getId();
            if ($isCorrect) {
                $score++;
            }

            $correction[] = [
                'questionId' => $question->getId(),
                'questionLabel' => $question->getLabel(),
                'selected' => $selectedRep ? ['id' => $selectedRep->getId(), 'label' => $selectedRep->getLabel()] : null,
                'correct' => $correctRep ? ['id' => $correctRep->getId(), 'label' => $correctRep->getLabel()] : null,
                'isCorrect' => $isCorrect,
            ];
        }

        $attempt = new QcmAttempt();
        $attempt->setUser($user);
        $attempt->setQcm($qcm);
        $attempt->setScore($score);
        $attempt->setTotal($total);
        $attempt->setSubmittedAt(new \DateTimeImmutable());

        $em->persist($attempt);
        $em->flush();

        return $this->json([
            'attemptId' => $attempt->getId(),
            'score' => $score,
            'total' => $total,
            'correction' => $correction,
        ]);
    }
}
