<?php

namespace App\Controller\AppController;

use App\Controller\ApiController\UserApiController;
use App\Entity\User;
use App\Form\LoginType;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
class UserController extends AbstractController
{
    public const JWT_SESSION_KEY = 'jwt';
    private UserApiController $userApiController;

    public function __construct(UserApiController $userApiController)
    {
        $this->userApiController = $userApiController;
    }

    /**
     * @Route("/sign_up", name="sign_up")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function signUp(Request $request): RedirectResponse|Response
    {
        $session = $request->getSession();
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $data = [
                User::USERNAME_ATTR => $user->getUsername(),
                User::PASSWORD_ATTR => $user->getPassword()
            ];

            $request = Request::create(
                UserApiController::USER_API_ROUTE,
                'POST',
                [], [], [], [],
                json_encode($data)
            );

            $response = $this->userApiController->postAction($request);

            if ($response->getStatusCode() == Response::HTTP_CREATED) {

                $request = Request::create(
                    UserApiController::LOGIN_API_ROUTE,
                    'POST',
                    [], [], [], [],
                    json_encode($data)
                );
                $response = $this->userApiController->loginAction($request);

                if ($response->getStatusCode() == Response::HTTP_OK) {
                    $token = $response->headers->get('Authorization');
                    $session->set(self::JWT_SESSION_KEY, $token);
                    $session->set(User::USERNAME_ATTR, $user->getUsername());

                    return $this->redirectToRoute('search', []);
                }
            }
            $this->addFlash('error', 'Oops! Something went wrong and the registration could not be completed.');
        }
        return $this->render('user/sign-up.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/login", name="login")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function login(Request $request): RedirectResponse|Response
    {
        $session = $request->getSession();

        $form = $this->createForm(LoginType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $data = [
                User::USERNAME_ATTR => $formData['username'],
                User::PASSWORD_ATTR => $formData['password']
            ];

            $request = Request::create(
                UserApiController::LOGIN_API_ROUTE,
                'POST',
                [], [], [], [],
                json_encode($data)
            );

            $response = $this->userApiController->loginAction($request);

            if ($response->getStatusCode() == Response::HTTP_OK) {
                $token = $response->headers->get('Authorization');
                $session->set(self::JWT_SESSION_KEY, $token);
                $login = json_decode($response->getContent(), true);
                $session->set(UserApiController::USER_ID, $login[UserApiController::USER_ID]);
                $session->set(User::USERNAME_ATTR, $formData['username']);

                return $this->redirectToRoute('search', []);
            } else if ($response->getStatusCode() == Response::HTTP_UNAUTHORIZED) {
                $this->addFlash('error', 'Wrong credentials.');
            } else {
                $this->addFlash('error', 'Oops! Something went wrong and the login could not be completed.');
            }
        }

        return $this->render('user/login.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function logout(Request $request): RedirectResponse|Response
    {
        $session = $request->getSession();
        $session->clear();

        return $this->redirectToRoute('search', []);
    }
}