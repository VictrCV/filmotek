<?php

namespace App\Controller\AppController;

use App\Form\SearchFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
class SearchController extends AbstractController
{

    /**
     * @Route("/", name="search")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function search(Request $request): RedirectResponse|Response
    {
        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
        }

        return $this->render('search/search.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}