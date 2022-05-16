<?php

namespace App\Controller\AppController;

use App\Controller\ApiController\UserApiController;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
class AuthenticationController extends AbstractController
{
    private UserApiController $userApiController;

    public function __construct(UserApiController $userApiController)
    {
        $this->userApiController = $userApiController;
    }

    /**
     * @Route("/sign-up", name="sign_up")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function signUp(Request $request): RedirectResponse|Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->add('sign-up', SubmitType::class, [
            'label' => 'Sign up',
            'attr' => [
                'class' => 'btn-sign-up-form'
            ],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $data = [
                User::USERNAME_ATTR => $user->getUsername(),
                User::PASSWORD_ATTR => $user->getPassword()
            ];

            $request = Request::create(
                UserApiController::USER_API_ROUTE,
                'POST',
                [],
                [],
                [],
                [],
                strval(json_encode($data))
            );

            $response = $this->userApiController->postAction($request);

            if ($response->getStatusCode() == Response::HTTP_CREATED) {
                return $this->redirectToRoute('search', []);
            } else {
                $this->addFlash('error', 'Oops! Something went wrong and the registration could not be completed.');
            }
        }
        return $this->render('authentication/sign-up.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}