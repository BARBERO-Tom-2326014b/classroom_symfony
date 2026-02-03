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

Format JSON STRICT attendu :
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

        $data = $response->toArray();

        // réponse IA → texte → JSON
        $content = $data['choices'][0]['message']['content'] ?? null;

        if (!$content) {
            throw new \RuntimeException('Réponse Mistral invalide');
        }

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }
}
