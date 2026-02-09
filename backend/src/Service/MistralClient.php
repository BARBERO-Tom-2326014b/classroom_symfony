<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MistralClient
{
    private const API_URL = 'https://api.mistral.ai/v1/chat/completions';

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $mistralApiKey
    ) {}

    public function generateQcm(string $text, int $questionCount = 5, bool $allowBoolean = true): array
    {
        $questionCount = max(2, min(20, $questionCount));

        $booleanRule = $allowBoolean
            ? "Les questions peuvent être de type QCM (4 choix) OU Vrai/Faux (2 choix)."
            : "Les questions doivent être uniquement des QCM à 4 choix. Pas de Vrai/Faux.";

        $prompt = <<<PROMPT
À partir du texte suivant, génère un QCM de {$questionCount} questions.
$booleanRule

Règles:
- Chaque question doit avoir une propriété "label".
- Chaque question doit avoir une propriété "answers".
- Pour une question QCM: "answers" contient exactement 4 réponses.
- Pour une question Vrai/Faux: "answers" contient exactement 2 réponses: "Vrai" et "Faux".
- Chaque réponse a: { "label": string, "correct": boolean }
- Il doit y avoir UNE seule réponse correcte par question.

Format JSON STRICT attendu (sans markdown, sans texte autour) :
{
  "title": "Titre du QCM",
  "questions": [
    {
      "label": "Question ?",
      "answers": [
        { "label": "Réponse A", "correct": false },
        { "label": "Réponse B", "correct": true }
      ]
    }
  ]
}

Texte :
$text
PROMPT;

        $response = $this->httpClient->request('POST', self::API_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->mistralApiKey,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model' => 'mistral-small',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.4,
            ],
        ]);

        $data = $response->toArray(false);

        // Réponse API invalide
        $content = $data['choices'][0]['message']['content'] ?? null;
        if (!is_string($content) || trim($content) === '') {
            $maybeError = $data['error']['message'] ?? null;
            throw new \RuntimeException('Réponse Mistral invalide' . ($maybeError ? (': ' . $maybeError) : ''));
        }

        $json = $this->extractJsonObject($content);
        if ($json === null) {
            throw new \RuntimeException('Impossible d\'extraire un JSON de la réponse Mistral.');
        }

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    private function extractJsonObject(string $content): ?string
    {
        $content = trim($content);

        // Cas: ```json ... ```
        if (preg_match('/```(?:json)?\s*(\{.*})\s*```/s', $content, $m)) {
            return trim($m[1]);
        }

        // Cas: l'IA renvoie du texte + JSON -> on prend le premier { ... dernier }
        $start = strpos($content, '{');
        $end = strrpos($content, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        return trim(substr($content, $start, $end - $start + 1));
    }
}
