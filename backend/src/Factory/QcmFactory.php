<?php

namespace App\Factory;

use App\Entity\Qcm;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Entity\Document;

class QcmFactory
{
    public function createFromAiResponse(array $data, Document $document): Qcm
    {
        if (!isset($data['title'], $data['questions'])) {
            throw new \InvalidArgumentException('Structure QCM invalide');
        }

        // 1️⃣ QCM
        $qcm = new Qcm();
        $qcm->setTitle($data['title']);

        // Optionnel : garder une trace du PDF source
        $qcm->setSourcePdfName($document->getPdfName());

        // 2️⃣ Questions
        foreach ($data['questions'] as $questionData) {

            if (!isset($questionData['label'], $questionData['answers'])) {
                continue;
            }

            $question = new Question();
            $question->setLabel($questionData['label']);
            $question->setQcm($qcm);

            // 3️⃣ Réponses
            foreach ($questionData['answers'] as $answerData) {

                if (!isset($answerData['label'], $answerData['correct'])) {
                    continue;
                }

                $reponse = new Reponse();
                $reponse->setLabel($answerData['label']);
                $reponse->setIsCorrect((bool) $answerData['correct']);
                $reponse->setQuestion($question);

                $question->addReponse($reponse);
            }

            $qcm->addQuestion($question);
        }

        return $qcm;
    }
}
