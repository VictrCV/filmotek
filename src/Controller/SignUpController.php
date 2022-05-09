<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SignUpController extends AbstractController
{
    /**
     * @Route("/sign-up", name="sign_up")
     */
    public function signUp()
    {
        return $this->render('sign-up/sign-up.html.twig');
    }
}