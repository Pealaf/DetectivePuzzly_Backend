<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAiApiController extends AbstractController
{
    /**
     * Cette méthode permet de faire une requête à l'API ChatGPT afin de générer une énigme
     *
     * @param HttpClientInterface $httpClient
     * @return JsonResponse
     */
    #[Route('/api/external/openAi', name: 'external_api', methods: 'GET')]
    public function genererEnigme(HttpClientInterface $httpClient): JsonResponse
    {
        $apiKey = $_ENV['OPEN_AI_API_KEY'];

        $url = 'https://api.openai.com/v1/chat/completions';
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ];

        $body = json_encode([
            'model' => 'gpt-4-turbo',
            'messages' => [
                ['role' => 'system', 'content' => "Ta réponse doit être un objet json. Cet objet contient un champ 'enigme' pour l'énigme, un champ 'options' qui contient les propositions de réponses et un champ 'reponse_correcte' pour la réponse correcte."],
                ['role' => 'user', 'content' => "Génère une énigme avec 4 solutions possibles dont 3 fausses et une vraie. Précise quelle est la réponse vraie."]
            ],
            'max_tokens' => 600,
            'temperature' => 1.1
        ]);

        try {
            $response = $httpClient->request(
                'POST',
                $url,
                [
                    'headers' => $headers,
                    'body' => $body,
                ]
            );
            return new JsonResponse($response->getContent(), $response->getStatusCode(), [], true);
        } catch (TransportExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
        }

        return new JsonResponse([]);
    }
}