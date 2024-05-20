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
    private $themes = [
        'la nature',
        'les mathématiques',
        'la littérature',
        'l\'histoire',
        'les sciences',
        'les animaux',
        'la technologie',
        'la géographie',
        'la cuisine',
        'la musique',
        'la mythologie',
        'les films et séries',
        'les sports',
        'les arts',
        'les inventions',
        'les langues',
        'les mystères et détectives',
        'la philosophie',
        'la vie quotidienne',
        'les célébrités',
        'les jeux vidéo',
        'l\'espace et l\'astronomie',
        'la médecine',
        'la botanique',
        'l\'architecture',
        'les chiffres et codes',
        'les festivals et célébrations',
        'les couleurs',
        'les métaphores et expressions',
        'les contes et légendes',
        'l\'écologie',
        'la psychologie',
        'la mode et les vêtements',
        'le transport',
        'le climat et la météo',
        'les lois et la justice',
        'l\'agriculture',
        'les parfums et senteurs',
        'les énigmes logiques',
        'les mythes urbains',
        'la poésie',
        'la politique',
        'l\'économie',
        'les religions',
        'l\'artisanat',
        'les relations humaines',
        'les énigmes visuelles',
        'le langage des fleurs',
        'les cartes et la navigation',
        'les symboles et emblèmes',
    ];

    function genererNombreAleatoire() {
        return mt_rand(0, sizeof($this->themes)-1);
    }

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

        // Récupération d'un thème aléatoire
        $theme = $this -> themes[$this->genererNombreAleatoire()];

        $body = json_encode([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => "Ta réponse doit être un objet json. Cet objet contient un champ 'enigme' pour l'énigme, un champ 'options' qui contient les propositions de réponses et un champ 'reponse_correcte' pour la réponse correcte."],
                ['role' => 'user', 'content' => "Génère une énigme sur " . $theme . " avec 4 solutions possibles dont 3 fausses et une vraie. Précise quelle est la réponse vraie."]
            ],
            'temperature' => 0.5,
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