<?php

namespace App\Controller;

use App\Entity\Enigme;
use App\Repository\EnigmeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EnigmeController extends AbstractController
{
    #[Route('/api/enigmes', name: 'enigmes', methods: ['GET'])]
    public function getAllEnigmes(EnigmeRepository $enigmeRepository, SerializerInterface $serializer): JsonResponse
    {
        $enigmes = $enigmeRepository->findAll();
        $jsonEnigmes = $serializer->serialize($enigmes, 'json', ['groups' => 'getEnigmes']);
        return new JsonResponse($jsonEnigmes, Response::HTTP_OK, [], true);
    }

    #[Route('/api/enigmes/{id}', name: 'detailEnigme', methods: ['GET'])]
    public function getDetailEnigme(Enigme $enigme, SerializerInterface $serializer): JsonResponse
    {
        $jsonEnigme = $serializer->serialize($enigme, 'json'/*, ['groups' => 'getBooks']*/);
        return new JsonResponse($jsonEnigme, Response::HTTP_OK, [], true);
    }

    #[Route('/api/enigmes/{id}', name: 'deleteEnigme', methods: ['DELETE'])]
    public function deleteEnigme(Enigme $enigme, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($enigme);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/enigmes', name:"createEnigme", methods: ['POST'])]
    public function createEnigme(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $enigme = $serializer->deserialize($request->getContent(), Enigme::class, 'json');
        $em->persist($enigme);
        $em->flush();

        $jsonEnigme = $serializer->serialize($enigme, 'json'/*, ['groups' => 'getBooks']*/);

        $location = $urlGenerator->generate('detailEnigme', ['id' => $enigme->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonEnigme, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    private function createEnigmeFromObject(Enigme $enigme, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $em->persist($enigme);
        $em->flush();

        $jsonEnigme = $serializer->serialize($enigme, 'json', ['groups' => 'getEnigmes']);

        $location = $urlGenerator->generate('detailEnigme', ['id' => $enigme->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonEnigme, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/enigmes/{id}', name:"updateEnigme", methods:['PUT'])]
    public function updateEnigme(Request $request, SerializerInterface $serializer, Enigme $currentEnigme, EntityManagerInterface $em, UserRepository $userRepository): JsonResponse
    {
        $updatedEnigme = $serializer->deserialize($request->getContent(),
            Enigme::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentEnigme]);
        $content = $request->toArray();
        $user = $content['utilisateur'] ?? -1;
        $updatedEnigme->setUtilisateur($userRepository->find($user['id']));

        $em->persist($updatedEnigme);
        $em->flush();

        $jsonUpdatedEnigme = $serializer->serialize($updatedEnigme, 'json', ['groups' => 'getEnigmes']);
        return new JsonResponse($jsonUpdatedEnigme, JsonResponse::HTTP_OK);
    }

    #[Route('/api/enigmes/generate/{idUser}', name: 'generateEnigme', methods: ['GET'])]
    public function generateEnigme(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, EnigmeRepository $enigmeRepository, UserRepository $userRepository, HttpClientInterface $httpClient): JsonResponse
    {
        // Récupération de l'utilisateur
        $user = $userRepository-> find($request->get('idUser'));
        // Génération de l'énigme
        $newEnigmeGenerated = json_decode($enigmeRepository->genererEnigme($httpClient)->getContent());
        // Création de l'objet Enigme
        $newEnigme = new Enigme();
        $newEnigme -> setIntitule($newEnigmeGenerated->enigme);
        $newEnigme -> setReponseA($newEnigmeGenerated->options[0]);
        $newEnigme -> setReponseB($newEnigmeGenerated->options[1]);
        $newEnigme -> setReponseC($newEnigmeGenerated->options[2]);
        $newEnigme -> setReponseD($newEnigmeGenerated->options[3]);
        $newEnigme -> setSolution($newEnigmeGenerated->reponse_correcte);
        $newEnigme -> setUtilisateur($user);
        $newEnigme -> setResolue(false);
        // Création de l'énigme en BDD
        $jsonResponse = $this -> createEnigmeFromObject($newEnigme, $serializer, $em, $urlGenerator);

        return new JsonResponse(json_decode($jsonResponse -> getContent()), JsonResponse::HTTP_OK);
    }

    #[Route('/api/enigmes/nonResolues/{idUser}', name: 'getAllEnigmesNonResoluesByUser', methods: ['GET'])]
    public function getAllEnigmesNonResoluesByUser(Request $request, SerializerInterface $serializer, EnigmeRepository $enigmeRepository, UserRepository $userRepository): JsonResponse
    {
        // Récupération de l'utilisateur
        $user = $userRepository-> find($request->get('idUser'));

        return $enigmeRepository->getEnigmesNonResoluesByUser($user, $serializer);
    }

    #[Route('/api/enigmes/count/{idUser}', name: 'countEnigmesByUser', methods: ['GET'])]
    public function countEnigmesByUser(Request $request, EnigmeRepository $enigmeRepository): JsonResponse
    {
        // Récupération de l'id de l'utilisateur
        $idUtilisateur = $request->get('idUser');

        $nbEnigmes = $enigmeRepository -> countEnigmesByUtilisateur($idUtilisateur);
        $nbEnigmesResolues = $enigmeRepository -> countEnigmesResoluesByUtilisateur($idUtilisateur);

        $json["nombreEnigmes"] = $nbEnigmes;
        $json["nombreEnigmesResolues"] = $nbEnigmesResolues;

        return new JsonResponse($json, JsonResponse::HTTP_OK);
    }
}