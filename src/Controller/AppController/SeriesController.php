<?php

namespace App\Controller\AppController;

use App\Controller\ApiController\SeriesApiController;
use App\Entity\Series;
use App\Utility\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @codeCoverageIgnore
 */
class SeriesController extends AbstractController
{
    protected HttpClientInterface $client;
    protected SeriesApiController $seriesApiController;

    public function __construct(
        HttpClientInterface $client,
        SeriesApiController $seriesApiController
    )
    {
        $this->client = $client;
        $this->seriesApiController = $seriesApiController;
    }

    /**
     * @Route("/series/{apiId}", name="series")
     * @param string $apiId
     * @return RedirectResponse|Response
     */
    public function series(string $apiId): RedirectResponse|Response
    {
        $request = Request::create(
            SeriesApiController::SERIES_GET_BY_API_ID_ROUTE . $apiId
        );
        $response = $this->seriesApiController->getByApiIdAction($request, $apiId);

        if ($response->getStatusCode() == Response::HTTP_NOT_FOUND) {
            $series = $this->getSeriesFromRapidapi($apiId);
            if (!isset($series)) {
                return new Response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $series = json_decode($response->getContent(), true)[Series::SERIES_ATTR];
        }

        return $this->render('series/series.html.twig', [
            'series' => $series
        ]);
    }

    protected function getSeriesFromRapidapi(string $apiId): ?array
    {
        $response = $this->client->request(
            'GET',
            SearchController::RAPIDAPI_BASE_URL . '/titles/' . $apiId,
            [
                'headers' => [
                    'X-RapidAPI-Host' => SearchController::RAPIDAPI_HOST,
                    'X-RapidAPI-Key' => $_ENV['RAPIDAPI_KEY']
                ],
                'query' => [
                    'info' => 'base_info'
                ]
            ]
        );

        if ($response->getStatusCode() == Response::HTTP_OK) {
            return Utils::rapidapiJsonToSeriesArray($response->getContent());
        } else {
            $this->addFlash('error', 'Oops! Something went wrong and the series could not be obtained.');
            return null;
        }
    }
}