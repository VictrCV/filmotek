<?php

namespace App\Controller\AppController;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{

    /**
     * @Route("/", name="search")
     */
    public function search()
    {
        return $this->render('search/search.html.twig');
    }
}