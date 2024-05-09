<?php

namespace App\Repository;

use App\Controller\OpenAiApiController;
use App\Entity\Enigme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @extends ServiceEntityRepository<Enigme>
 */
class EnigmeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enigme::class);
    }

    /**
     * Used to generate an enigme with ChatGPT
     */
    public function genererEnigme(HttpClientInterface $httpClient): JsonResponse
    {
        // Création du contrôleur de l'API
        $openAiApiController = new OpenAiApiController();
        // Génération d'une énigme
        $jsonResponse = $openAiApiController->genererEnigme($httpClient);
        // Traitement de la réponse
        $nouvelleEnigme = json_decode($jsonResponse->getContent())->choices[0]->message->content;

        return new JsonResponse($nouvelleEnigme, Response::HTTP_OK, [], true);
    }
}