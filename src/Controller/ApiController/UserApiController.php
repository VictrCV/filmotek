<?php

namespace App\Controller\ApiController;

use App\Entity\User;
use App\Utility\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * @package App\Controller\ApiController
 * @Route(path=UserApiController::USER_API_ROUTE, name="user_api_")
 */
class UserApiController extends AbstractController
{
    public const USER_API_ROUTE = '/api/v1/users';
    public const LOGIN_API_ROUTE = UserApiController::USER_API_ROUTE . '/login';
    public const USER_ID = 'userId';

    private const HEADER_CACHE_CONTROL = 'Cache-Control';
    private const HEADER_ALLOW = 'Allow';

    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private AuthenticationSuccessHandler $successHandler;
    private AuthenticationFailureHandler $failureHandler;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(
        EntityManagerInterface       $em,
        UserPasswordHasherInterface  $passwordHasher,
        AuthenticationSuccessHandler $successHandler,
        AuthenticationFailureHandler $failureHandler
    )
    {
        $this->entityManager = $em;
        $this->passwordHasher = $passwordHasher;
        $this->successHandler = $successHandler;
        $this->failureHandler = $failureHandler;
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
        $user->setUsername(strval($data[User::USERNAME_ATTR]));
        $user->setPassword(strval($data[User::PASSWORD_ATTR]));
        $password = $this->passwordHasher->hashPassword($user, $user->getPassword());
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
        $methods = ['OPTIONS', 'POST'];

        return new Response(
            null,
            Response::HTTP_NO_CONTENT,
            [
                self::HEADER_ALLOW => implode(', ', $methods),
                self::HEADER_CACHE_CONTROL => 'public, inmutable'
            ]
        );
    }

    /**
     * @param Request $request
     * @return JWTAuthenticationSuccessResponse|Response
     * @Route(path="/login", name="login", methods={"POST"})
     */
    public function loginAction(Request $request): JWTAuthenticationSuccessResponse|Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        if (!isset($data[User::USERNAME_ATTR], $data[User::PASSWORD_ATTR])) {
            return Utils::errorMessage(Response::HTTP_UNPROCESSABLE_ENTITY, "Missing data.");
        }

        $username = $data[User::USERNAME_ATTR];
        $password = $data[User::PASSWORD_ATTR];

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy([User::USERNAME_ATTR => $username]);

        if (!isset($user) || !$this->passwordHasher->isPasswordValid($user, strval($password))) {
            return $this->failureHandler->onAuthenticationFailure(
                $request,
                new BadCredentialsException()
            );
        }

        $response = $this->successHandler->handleAuthenticationSuccess($user);
        $jwt = json_decode((string)$response->getContent())->token;
        $response->setData(
            [
                self::USER_ID => $user->getId(),
                'token_type' => 'Bearer',
                'access_token' => $jwt,
                'expires_in' => 2 * 60 * 60,
            ]
        );

        $response->headers->set('Authorization', 'Bearer ' . $jwt);
        return $response;
    }

}