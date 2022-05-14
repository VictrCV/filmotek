<?php

namespace App\Controller\ApiController;

use App\Entity\User;
use App\Utility\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserApiController
 * @package App\Controller\ApiController
 * @Route(path=UserApiController::USER_API_ROUTE, name="user_api_")
 */
class UserApiController extends AbstractController
{
    public const USER_API_ROUTE = '/api/v1/users';

    private const HEADER_CACHE_CONTROL = 'Cache-Control';
    private const HEADER_ALLOW = 'Allow';

    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $em;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(path="", name="post", methods={"POST"})
     */
    public function postAction(Request $request): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        if (!isset($data[User::USERNAME_ATTR], $data[User::PASSWORD_ATTR])) {
            return Utils::errorMessage(Response::HTTP_UNPROCESSABLE_ENTITY, "Missing data.");
        }

        $user = new User();
        $user->setUsername($data[User::USERNAME_ATTR]);
        $password = $this->passwordHasher->hashPassword($user, $data[User::PASSWORD_ATTR]);
        $user->setPassword($password);

        $usernameExists = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy([User::USERNAME_ATTR => $user->getUsername()]);

        if ($usernameExists !== null) {
            return Utils::errorMessage(Response::HTTP_BAD_REQUEST, "Username already exists.");
        } else {
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return Utils::apiResponse(
                Response::HTTP_CREATED,
                [User::USER_ATTR => $user]
            );
        }
    }

    /**
     * @return Response
     * @Route(path="", name="options", methods={"OPTIONS"})
     */
    public function optionsAction(): Response
    {
        $methods = ['POST'];
        $methods[] = 'OPTIONS';

        return new Response(
            null,
            Response::HTTP_NO_CONTENT,
            [
                self::HEADER_ALLOW => implode(', ', $methods),
                self::HEADER_CACHE_CONTROL => 'public, inmutable'
            ]
        );
    }

}