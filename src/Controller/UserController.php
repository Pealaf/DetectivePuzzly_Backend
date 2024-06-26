<?php

namespace App\Controller;

use App\Entity\User;
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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'users', methods: ['GET'])]
    public function getAllUsers(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $userRepository->findAll();
        $jsonUsers = $serializer->serialize($users, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])]
    public function getDetailUser(User $user, SerializerInterface $serializer): JsonResponse
    {
        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/login/{login}', name: 'userByLogin', methods: ['GET'])]
    public function getUserByLogin(Request $request, SerializerInterface $serializer, UserRepository $userRepository): JsonResponse
    {
        $login = $request->get('login');
        return $userRepository->getUserByLogin($login, $serializer);
    }

    #[Route('/api/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(Request $request, EntityManagerInterface $em, UserRepository $userRepository): JsonResponse
    {
        // Récupération de l'utilisateur
        $user = $userRepository->find($request->get('id'));

        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/users', name:"createUser", methods: ['POST'])]
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, UserPasswordHasherInterface $userPasswordHashed): JsonResponse
    {

        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $user->setPassword($userPasswordHashed->hashPassword($user, $user->getPassword()));
        $em->persist($user);
        $em->flush();

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);

        $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/users/{id}', name:"updateUser", methods:['PUT'])]
    public function updateUser(Request $request, SerializerInterface $serializer, User $currentUser, EntityManagerInterface $em, UserRepository $userRepository): JsonResponse
    {
        $updatedUser = $serializer->deserialize($request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]);
        $content = $request->toArray();
        $user = $content['utilisateur'] ?? -1;
        $updatedUser->setAuthor($userRepository->find($user));

        $em->persist($updatedUser);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/api/users/updatePassword/{id}', name:"updatePassword", methods:['POST'])]
    public function updatePassword(Request $request, EntityManagerInterface $em, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHashed): JsonResponse
    {
        // Récupération de l'utilisateur
        $user = $userRepository->find($request->get('id'));
        // Récupération des données
        $data = $request->toArray();
        $newPassword = $data["newPassword"];
        $oldPassword = $data["oldPassword"];

        // Vérification de l'ancien mot de passe
        if (!$userPasswordHashed->isPasswordValid($user, $oldPassword)) {
            // Si l'ancien mot de passe ne correspond pas, on retourne une erreur
            return new JsonResponse("", Response::HTTP_INTERNAL_SERVER_ERROR, [], true);
        }

        // Modification du mot de passe
        $user->setPassword($newPassword);
        $user->setPassword($userPasswordHashed->hashPassword($user, $user->getPassword()));
        // Mise à jour de l'utilisateur
        $em->persist($user);
        $em->flush();

        return new JsonResponse("", Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/get/top', name: 'getTopUsers', methods: ['GET'])]
    public function getTopUsers(EntityManagerInterface $em, UserRepository $userRepository): JsonResponse
    {
        $topUsers = $userRepository -> getTopUsers($em);
        return new JsonResponse($topUsers, JsonResponse::HTTP_OK);
    }
}
