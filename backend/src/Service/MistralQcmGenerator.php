<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MistralQcmGenerator
{
    public function __construct(
        private HttpClientInterface $client,
        private string $mistralApiKey
    ) {}

    public function generate(string $text): array
    {
        $response = $this->client->request(
            'POST',
            'https://api.mistral.ai/v1/chat/completions',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->mistralApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'mistral-small',
                    'temperature' => 0.3,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Tu es un professeur. Tu génères des QCM.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $this->buildPrompt($text)
                        ]
                    ],
                ],
            ]
        );

        return $response->toArray();
    }

    private function buildPrompt(string $text): string
    {
        return <<<PROMPT
À partir du texte suivant, génère EXACTEMENT 5 questions de QCM.

FORMAT STRICT JSON :
{
  "title": "Titre du QCM",
  "questions": [
    {
      "label": "Question ?",
      "answers": [
        { "label": "Réponse A", "isCorrect": false },
        { "label": "Réponse B", "isCorrect": true }
      ]
    }
  ]
}

Texte :
$text
PROMPT;
    }
}
