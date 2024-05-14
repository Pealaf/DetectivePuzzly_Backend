<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Used to know if a login is used.
     */
    public function getUserByLogin(String $login, SerializerInterface $serializer): JsonResponse
    {
        if ($login == null) {
            throw new UnsupportedUserException(sprintf('Login invalide'));
        }
        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy(['login' => $login]);

        // Vérifie si le user existe
        if (!$user) {
            // Retourne une réponse HTTP 404 si le user n'est pas trouvé
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    /**
     * Méthode permettant de récupérer les 5 meilleurs joueurs
     * @param EntityManagerInterface $entityManager
     * @return mixed
     */
    public function getTopUsers(EntityManagerInterface $entityManager): mixed
    {
        $query = $entityManager->createQuery('
            SELECT u.login, COUNT(e.id) AS nbEnigmesResolues
            FROM App\Entity\User u
            LEFT JOIN App\Entity\Enigme e WITH u.id = e.utilisateur
            WHERE e.resolue = 1
            GROUP BY u.id
            ORDER BY nbEnigmesResolues DESC
        ')->setMaxResults(5);

        return $query->getResult();
    }
}