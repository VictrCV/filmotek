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
     * @Route("{list}/series/{apiId}", name="series")
     * @param Request $request
     * @param string $apiId
     * @return RedirectResponse|Response
     */
    public function series(Request $request, string $list, string $apiId): RedirectResponse|Response
    {
        $session = $request->getSession();

        $request = Request::create(
            SeriesApiController::SERIES_GET_BY_API_ID_ROUTE . $apiId
        );
        $response = $this->seriesApiController->getByApiIdAction($request, $apiId);

        if ($response->getStatusCode() == Response::HTTP_NOT_FOUND) {
            $series = $this->getSeriesFromRapidapi($apiId);
            if (!isset($series)) {
                $this->addFlash('error', 'Oops! Something went wrong and the series could not be obtained.');
                return $this->redirectToRoute($list);
            }
        } else {
            $series = json_decode($response->getContent(), true)[Series::SERIES_ATTR];
        }

        if (isset($series['id']) && $session->get(UserApiController::USER_ID) !== null) {
            $userId = $session->get(UserApiController::USER_ID);

            $inFavourites = $this->isSeriesInList($userId, SeriesList::FAVOURITES, $series['id']);
            $inIncompatibleList = $this->isSeriesInIncompatibleList($userId, $series['id']);

            if ($list != 'search') {
                $seriesList = $this->getSeriesList($list, $userId, $series['id']);
                if (!isset($seriesList)) {
                    $this->addFlash('error', 'Oops! Something went wrong and the series could not be loaded.');
                    return $this->redirectToRoute($list);
                }
            } else {
                $seriesList = null;
            }
        } else {
            $inFavourites = false;
            $inIncompatibleList = false;
            $seriesList = null;
        }

        return $this->render('series/series.html.twig', [
            'series' => $series,
            'inFavourites' => $inFavourites,
            'inIncompatibleList' => $inIncompatibleList,
            'seriesList' => $seriesList
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

    protected function isSeriesInList(int $userId, string $type, int $seriesId): bool
    {
        $data = [
            SeriesList::TYPE_ATTR => $type,
            SeriesList::SERIES_ATTR => $seriesId,
        ];

        $request = Request::create(
            SeriesListApiController::SERIES_LIST_GET_BY_USER_ROUTE . $userId,
            'GET',
            $data
        );

        $response = $this->seriesListApiController->getByUserAction($request, $userId);

        return $response->getStatusCode() == Response::HTTP_OK;
    }

    protected function isSeriesInIncompatibleList(int $userId, int $seriesId): bool
    {
        return $this->isSeriesInList($userId, SeriesList::TO_WATCH, $seriesId) ||
            $this->isSeriesInList($userId, SeriesList::IN_PROGRESS, $seriesId);
    }

    protected function getSeriesList(string $list, int $userId, int $seriesId): ?array
    {
        $data = [SeriesList::SERIES_ATTR => $seriesId];
        switch ($list) {
            case 'favourites':
                $data[SeriesList::TYPE_ATTR] = SeriesList::FAVOURITES;
                break;
            case 'to_watch':
                $data[SeriesList::TYPE_ATTR] = SeriesList::TO_WATCH;
                break;
            case 'in_progress':
                $data[SeriesList::TYPE_ATTR] = SeriesList::IN_PROGRESS;
                break;
        }

        $request = Request::create(
            SeriesListApiController::SERIES_LIST_GET_BY_USER_ROUTE . $userId,
            'GET',
            $data
        );
        $response = $this->seriesListApiController->getByUserAction($request, $userId);

        return $response->getStatusCode() == Response::HTTP_OK
            ? json_decode($response->getContent(), true)[SeriesList::SERIES_LIST_ATTR][0]
            : null;
    }
}