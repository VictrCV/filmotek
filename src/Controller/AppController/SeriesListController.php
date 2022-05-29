<?php

namespace App\Controller\AppController;

use App\Controller\ApiController\SeriesApiController;
use App\Controller\ApiController\SeriesListApiController;
use App\Controller\ApiController\UserApiController;
use App\Entity\Series;
use App\Entity\SeriesList;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
class SeriesListController extends SeriesController
{

    /**
     * @Route("/series-list/post/{type}/{apiId}", name="series_list_post")
     * @param Request $request
     * @param string $type
     * @param string $apiId
     * @return RedirectResponse|Response
     */
    public function postSeriesList(Request $request, string $type, string $apiId): RedirectResponse|Response
    {
        $session = $request->getSession();

        $request = Request::create(
            SeriesApiController::SERIES_GET_BY_API_ID_ROUTE . $apiId
        );
        $response = $this->seriesApiController->getByApiIdAction($request, $apiId);

        if ($response->getStatusCode() == Response::HTTP_NOT_FOUND) {
            $series = $this->getSeriesFromRapidapi($apiId);
            if (isset($series)) {
                return new Response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $data = [
                Series::API_ID_ATTR => $series[Series::API_ID_ATTR],
                Series::TITLE_ATTR => $series[Series::TITLE_ATTR],
                Series::IS_FILM_ATTR => $series[Series::IS_FILM_ATTR],
                Series::SYNOPSIS_ATTR => $series[Series::SYNOPSIS_ATTR],
                Series::IMAGE_URL_ATTR => $series[Series::IMAGE_URL_ATTR]
            ];

            $request = Request::create(
                SeriesApiController::SERIES_API_ROUTE,
                'POST',
                [], [], [], [],
                json_encode($data)
            );
            $response = $this->seriesApiController->postAction($request);

            if ($response->getStatusCode() == Response::HTTP_CREATED) {
                $series = json_decode($response->getContent(), true)[Series::SERIES_ATTR];
            } else {
                $this->addFlash('error', 'Oops! Something went wrong and the series could not be created.');
                return $this->render('series/series.html.twig', [
                    'series' => $series,
                    'inFavourites' => false
                ]);
            }
        } else {
            $series = json_decode($response->getContent(), true)[Series::SERIES_ATTR];
        }

        $data = [
            SeriesList::TYPE_ATTR => $type,
            SeriesList::SERIES_ATTR => $series['id'],
            SeriesList::USER_ATTR => $session->get(UserApiController::USER_ID)
        ];

        $request = Request::create(
            SeriesListApiController::SERIES_LIST_API_ROUTE,
            'POST',
            [], [], [],
            ['HTTP_Authorization' => $session->get(UserController::JWT_SESSION_KEY)],
            json_encode($data)
        );
        $response = $this->seriesListApiController->postAction($request);

        $inFavourites = $this->isSeriesInList(
            $session->get(UserApiController::USER_ID),
            SeriesList::FAVOURITES,
            $series['id']);

        if ($response->getStatusCode() != Response::HTTP_CREATED) {
            $this->addFlash('error', 'Oops! Something went wrong and the series could not be added to list.');
        }

        return $this->render('series/series.html.twig', [
            'series' => $series,
            'inFavourites' => $inFavourites
        ]);
    }
}