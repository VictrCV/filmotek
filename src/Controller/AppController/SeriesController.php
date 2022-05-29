<?php

namespace App\Controller\AppController;

use App\Controller\ApiController\SeriesApiController;
use App\Controller\ApiController\SeriesListApiController;
use App\Controller\ApiController\UserApiController;
use App\Entity\Series;
use App\Entity\SeriesList;
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
    protected SeriesListApiController $seriesListApiController;

    public function __construct(
        HttpClientInterface     $client,
        SeriesApiController     $seriesApiController,
        SeriesListApiController $seriesListApiController
    )
    {
        $this->client = $client;
        $this->seriesApiController = $seriesApiController;
        $this->seriesListApiController = $seriesListApiController;
    }

    /**
     * @Route("/series/{apiId}", name="series")
     * @param Request $request
     * @param string $apiId
     * @return RedirectResponse|Response
     */
    public function series(Request $request, string $apiId): RedirectResponse|Response
    {
        $session = $request->getSession();

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

        if (isset($series['id']) && $session->get(UserApiController::USER_ID) !== null) {
            $inFavourites = $this->isSeriesInList(
                $session->get(UserApiController::USER_ID),
                SeriesList::FAVOURITES,
                $series['id']);
            $inIncompatibleList = $this->isSeriesInIncompatibleList(
                $session->get(UserApiController::USER_ID),
                $series['id']);
        } else {
            $inFavourites = false;
            $inIncompatibleList = false;
        }

        return $this->render('series/series.html.twig', [
            'series' => $series,
            'inFavourites' => $inFavourites,
            'inIncompatibleList' => $inIncompatibleList
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
            $series = Utils::rapidapiJsonToSeriesArray($response->getContent());
        }
        if (isset($series)) {
            return $series;
        }

        return null;
    }

    protected function isSeriesInList(int $user, string $type, int $series): bool
    {
        $data = [
            SeriesList::TYPE_ATTR => $type,
            SeriesList::SERIES_ATTR => $series,
        ];

        $request = Request::create(
            SeriesListApiController::SERIES_LIST_GET_BY_USER_ROUTE . $user,
            'GET',
            $data
        );

        $response = $this->seriesListApiController->getByUserAction($request, $user);

        return $response->getStatusCode() == Response::HTTP_OK;
    }

    protected function isSeriesInIncompatibleList(int $user, int $series): bool
    {
        return $this->isSeriesInList($user, SeriesList::TO_WATCH, $series) ||
            $this->isSeriesInList($user, SeriesList::IN_PROGRESS, $series);
    }
}