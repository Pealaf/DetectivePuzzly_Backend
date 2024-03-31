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

class EnigmeController extends AbstractController
{
    #[Route('/api/enigmes', name: 'enigmes', methods: ['GET'])]
    public function getAllEnigmes(EnigmeRepository $enigmeRepository, SerializerInterface $serializer): JsonResponse
    {
        $enigmes = $enigmeRepository->findAll();
        $jsonEnigmes = $serializer->serialize($enigmes, 'json'/*, ['groups' => 'getBooks']*/);
        return new JsonResponse($jsonEnigmes, Response::HTTP_OK, [], true);

        /*return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/EnigmeController2.php',
        ]);*/
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

    /*#[Route('/api/enigmes', name:"createEnigme", methods: ['POST'])]
    public function createEnigme(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, UserRepository $userRepository): JsonResponse
    {
        $enigme = $serializer->deserialize($request->getContent(), Enigme::class, 'json');

        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();

        // Récupération de l'idAuthor. S'il n'est pas défini, alors on met -1 par défaut.
        $idAuthor = $content['idAuthor'] ?? -1;

        // On cherche l'auteur qui correspond et on l'assigne au livre.
        // Si "find" ne trouve pas l'auteur, alors null sera retourné.
        $enigme->setAuthor($userRepository->find($idAuthor));

        $em->persist($enigme);
        $em->flush();

        $jsonEnigme = $serializer->serialize($enigme, 'json', ['groups' => 'getBooks']);

        $location = $urlGenerator->generate('detailBook', ['id' => $enigme->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonEnigme, Response::HTTP_CREATED, ["Location" => $location], true);
    }*/

    #[Route('/api/enigmes/{id}', name:"updateEnigme", methods:['PUT'])]
    public function updateEnigme(Request $request, SerializerInterface $serializer, Enigme $currentEnigme, EntityManagerInterface $em, UserRepository $userRepository): JsonResponse
    {
        $updatedEnigme = $serializer->deserialize($request->getContent(),
            Enigme::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentEnigme]);
        $content = $request->toArray();
        $user = $content['utilisateur'] ?? -1;
        $updatedEnigme->setAuthor($userRepository->find($user));

        $em->persist($updatedEnigme);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}