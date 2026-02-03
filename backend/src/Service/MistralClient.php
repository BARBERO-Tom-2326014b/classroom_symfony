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

    public function generateQcm(string $text): array
    {
        $prompt = <<<PROMPT
À partir du texte suivant, génère un QCM de 5 questions.
Chaque question doit avoir 4 réponses, dont UNE seule correcte.

Format JSON STRICT attendu (sans markdown, sans texte autour) :
{
  "title": "Titre du QCM",
  "questions": [
    {
      "label": "Question ?",
      "answers": [
        { "label": "Réponse A", "correct": false },
        { "label": "Réponse B", "correct": true },
        { "label": "Réponse C", "correct": false },
        { "label": "Réponse D", "correct": false }
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
