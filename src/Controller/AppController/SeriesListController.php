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
     * @Route("{list}/series_list/post/{type}/{apiId}", name="series_list_post")
     * @param Request $request
     * @param string $list
     * @param string $type
     * @param string $apiId
     * @return RedirectResponse|Response
     */
    public function postSeriesList(Request $request, string $list, string $type, string $apiId): RedirectResponse|Response
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
                return $this->redirectToRoute('series', [
                    'list' => $list,
                    Series::API_ID_ATTR => $apiId
                ]);
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
                return $this->redirectToRoute('series', [
                    'list' => $list,
                    Series::API_ID_ATTR => $apiId
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
            [], [], [], [],
            json_encode($data)
        );
        $response = $this->seriesListApiController->postAction($request);

        if ($response->getStatusCode() != Response::HTTP_CREATED) {
            $this->addFlash('error', 'Oops! Something went wrong and the series could not be added to list.');
        }

        return $this->redirectToRoute('series', [
            'list' => $list,
            Series::API_ID_ATTR => $apiId
        ]);
    }

    /**
     * @param int $userId
     * @param string $type
     * @return RedirectResponse|Response
     */
    protected function loadSeriesList(int $userId, string $type): RedirectResponse|Response
    {
        $request = Request::create(
            SeriesListApiController::SERIES_LIST_GET_BY_USER_ROUTE . $userId,
            'GET',
            [SeriesList::TYPE_ATTR => $type]
        );
        $response = $this->seriesListApiController->getByUserAction($request, $userId);

        if ($response->getStatusCode() == Response::HTTP_NOT_FOUND) {
            return $this->render('series-list/series-list.html.twig', [
                'seriesList' => [],
            ]);
        } else if ($response->getStatusCode() != Response::HTTP_OK) {
            $this->addFlash('error', 'Oops! Something went wrong and the ' . $type . ' series list could not be obtained.');
            return $this->redirectToRoute('search', []);
        }

        $seriesList = json_decode($response->getContent(), true)[SeriesList::SERIES_LIST_ATTR];
        return $this->render('series-list/series-list.html.twig', [
            'seriesList' => $seriesList,
        ]);
    }

    /**
     * @Route("{list}/series_list/start_watching/{apiId}", name="series_list_start_watching")
     * @param Request $request
     * @param string $list
     * @param string $apiId
     * @return RedirectResponse|Response
     */
    public function startWatching(Request $request, string $list, string $apiId): RedirectResponse|Response
    {
        $errorMessage = 'Oops! Something went wrong and it was not possible to start watching the series.';
        $session = $request->getSession();
        $userId = $session->get(UserApiController::USER_ID);

        $request = Request::create(
            SeriesApiController::SERIES_GET_BY_API_ID_ROUTE . $apiId
        );
        $response = $this->seriesApiController->getByApiIdAction($request, $apiId);

        if ($response->getStatusCode() == Response::HTTP_OK) {
            $seriesId = json_decode($response->getContent(), true)[Series::SERIES_ATTR]['id'];

            $data = [
                SeriesList::TYPE_ATTR => SeriesList::TO_WATCH,
                SeriesList::SERIES_ATTR => $seriesId
            ];

            $request = Request::create(
                SeriesListApiController::SERIES_LIST_GET_BY_USER_ROUTE . $userId,
                'GET',
                $data,

            );
            $response = $this->seriesListApiController->getByUserAction($request, $userId);

            if ($response->getStatusCode() == Response::HTTP_OK) {
                $seriesListId = json_decode($response->getContent(), true)[SeriesList::SERIES_LIST_ATTR][0]['id'];

                $request = Request::create(
                    SeriesListApiController::SERIES_LIST_API_ROUTE . '/' . $seriesListId,
                    'PUT',
                    [], [], [], [],
                    json_encode([SeriesList::TYPE_ATTR => SeriesList::IN_PROGRESS])
                );
                $response = $this->seriesListApiController->putAction($request, $seriesListId);

                if ($response->getStatusCode() == Response::HTTP_OK) {
                    return $this->redirectToRoute('series', [
                        'list' => 'in_progress',
                        Series::API_ID_ATTR => $apiId
                    ]);
                }
            }
        }

        $this->addFlash('error', $errorMessage);
        return $this->redirectToRoute('series', [
            'list' => $list,
            Series::API_ID_ATTR => $apiId
        ]);
    }
}