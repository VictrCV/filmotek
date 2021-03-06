<?php

namespace App\Controller\AppController;

use App\Form\SearchFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @codeCoverageIgnore
 */
class SearchController extends AbstractController
{
    public const SEARCH_LIST = 'search';

    public const RAPIDAPI_BASE_URL = 'https://moviesdatabase.p.rapidapi.com';
    public const RAPIDAPI_HOST = 'moviesdatabase.p.rapidapi.com';

    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

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
            $title = $formData['title'];
            $exact = $formData['exact'] ? 'true' : null;
            $genre = $formData['genre'];
            $type = $formData['type'];

            $url = self::RAPIDAPI_BASE_URL . '/titles';
            if (isset($title)) {
                $url .= '/search/title/' . rawurlencode($title);
            }

            $response = $this->client->request(
                'GET',
                $url,
                [
                    'headers' => [
                        'X-RapidAPI-Host' => self::RAPIDAPI_HOST,
                        'X-RapidAPI-Key' => $_ENV['RAPIDAPI_KEY']
                    ],
                    'query' => [
                        'info' => 'base_info',
                        'limit' => 50,
                        'exact' => $exact,
                        'titleType' => $type,
                        'genre' => $genre,
                    ]
                ]
            );

            if ($response->getStatusCode() == Response::HTTP_OK) {
                $results = $response->toArray()['results'];
                return $this->render('search/results.html.twig', [
                    'results' => $results,
                ]);
            } else {
                $this->addFlash('error', 'Oops! Something went wrong and the search could not be done.');
            }
        }

        return $this->render('search/search.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}