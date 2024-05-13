<?php

namespace App\Repository;

use App\Controller\OpenAiApiController;
use App\Entity\Enigme;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
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

    /**
     * Méthode permettant de récupérer les énigmes non résolues d'un utilisateur
     */
    public function getEnigmesNonResoluesByUser(User $user, SerializerInterface $serializer): JsonResponse
    {
        $enigmes = $this->getEntityManager()->getRepository(Enigme::class)->findBy(['utilisateur' => $user, 'resolue' => false]);
        $jsonEnigmes = $serializer->serialize($enigmes, 'json', ['groups' => 'getEnigmes']);
        return new JsonResponse($jsonEnigmes, Response::HTTP_OK, [], true);
    }

    /**
     * Méthode permettant de compter le nombre d'énigmes pour un utilisateur
     * @param int $utilisateurId
     * @return int nombre d'énigmes
     */
    public function countEnigmesByUtilisateur(int $utilisateurId): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.utilisateur = :utilisateurId')
            ->setParameter('utilisateurId', $utilisateurId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Méthode permettant de retourner le nombre d'énigmes résolues pour un utilisateur
     * @param int $utilisateurId
     * @return int nombre d'énigmes résolues
     */
    public function countEnigmesResoluesByUtilisateur(int $utilisateurId): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.utilisateur = :utilisateurId')
            ->andWhere('e.resolue = 1')
            ->setParameter('utilisateurId', $utilisateurId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}