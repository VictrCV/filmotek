<?php

namespace App\Controller\AppController;

use App\Controller\ApiController\SeriesApiController;
use App\Controller\ApiController\SeriesListApiController;
use App\Controller\ApiController\UserApiController;
use App\Entity\Series;
use App\Entity\SeriesList;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @codeCoverageIgnore
 */
class RecommendationController extends AbstractController
{
    public const MAX_RECOMMENDED_GENRES = 3;
    public const RECOMMENDED_SERIES = 3;

    protected HttpClientInterface $client;
    protected SeriesApiController $seriesApiController;
    protected SeriesListApiController $seriesListApiController;

    public function __construct(
        HttpClientInterface     $client,
        SeriesApiController     $seriesApiController,
        SeriesListApiController $seriesListApiController,
    )
    {
        $this->client = $client;
        $this->seriesApiController = $seriesApiController;
        $this->seriesListApiController = $seriesListApiController;
    }

    /**
     * @Route("search/recommendation/", name="recommendation")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function recommendation(Request $request): RedirectResponse|Response
    {
        $userId = $request->getSession()->get(UserApiController::USER_ID);

        $request = Request::create(
            SeriesListApiController::SERIES_LIST_GET_BY_USER_ROUTE . $userId,
            'GET',
            [SeriesList::TYPE_ATTR => SeriesList::FAVOURITES]
        );
        $response = $this->seriesListApiController->getByUserAction($request, $userId);

        if ($response->getStatusCode() == Response::HTTP_NOT_FOUND) {
            $this->addFlash('error', 'It is not possible to make a recommendation if there are no series in Favourites.');
        } elseif ($response->getStatusCode() != Response::HTTP_OK) {
            $this->addFlash('error', 'Oops! Something went wrong and the favourites series could not be obtained.');
        } else {
            $seriesList = json_decode($response->getContent(), true)[SeriesList::SERIES_LIST_ATTR];
            $seriesArray = array_column($seriesList, SeriesList::SERIES_ATTR);
            $genres = $this->getGenres();

            foreach ($seriesArray as $series) {
                foreach ($series[Series::GENRES_ATTR] as $genre) {
                    $genres[$genre]++;
                }
            }

            arsort($genres);
            if (sizeof($genres) >= self::MAX_RECOMMENDED_GENRES) {
                $genres = array_slice($genres, 0, self::MAX_RECOMMENDED_GENRES);
            }

            $recommendedSeries = [];
            foreach ($genres as $genre => $count) {
                $recommendedSeries = $this->getRecommendedSeries($genre, false, $seriesArray, $recommendedSeries);
                $recommendedSeries = $this->getRecommendedSeries($genre, true, $seriesArray, $recommendedSeries);
            }

            return $this->render('search/results.html.twig', [
                'results' => $recommendedSeries,
            ]);
        }

        return $this->redirectToRoute('search', []);
    }

    protected function getGenres(): ?array
    {
        $response = $this->client->request(
            'GET',
            SearchController::RAPIDAPI_BASE_URL . '/titles/utils/genres',
            [
                'headers' => [
                    'X-RapidAPI-Host' => SearchController::RAPIDAPI_HOST,
                    'X-RapidAPI-Key' => $_ENV['RAPIDAPI_KEY']
                ]
            ]
        );

        $body = $response->toArray();
        $body = array_values($body)[0];
        array_shift($body);
        $genres = [];
        foreach ($body as $genre) {
            $genres[$genre] = 0;
        }

        return $genres;
    }

    protected function getRecommendedSeries(string $genre, bool $isFilm, array $seriesArray, array $recommendedSeries): array
    {
        $response = $this->client->request(
            'GET',
            SearchController::RAPIDAPI_BASE_URL . '/titles',
            [
                'headers' => [
                    'X-RapidAPI-Host' => SearchController::RAPIDAPI_HOST,
                    'X-RapidAPI-Key' => $_ENV['RAPIDAPI_KEY']
                ],
                'query' => [
                    'info' => 'base_info',
                    'limit' => 50,
                    'genre' => $genre,
                    'list' => $isFilm ? 'top_rated_250' : 'top_rated_series_250'
                ]
            ]
        );

        if ($response->getStatusCode() == Response::HTTP_OK) {
            $topSeries = json_decode($response->getContent(), true)['results'];

            foreach ($recommendedSeries as $series) {
                $seriesExists = array_search(
                    $series['id'],
                    array_column(
                        array_slice($topSeries, 0, self::RECOMMENDED_SERIES),
                        'id'
                    ));
                if ($seriesExists !== false) {
                    unset($topSeries[$seriesExists]);
                }
            }

            foreach ($seriesArray as $series) {
                $seriesExists = array_search(
                    $series[Series::API_ID_ATTR],
                    array_column(
                        array_slice($topSeries, 0, self::RECOMMENDED_SERIES),
                        'id'
                    ));
                if ($seriesExists !== false) {
                    unset($topSeries[$seriesExists]);
                }
            }

            $recommendedSeries = array_merge(
                $recommendedSeries,
                array_slice($topSeries, 0, self::RECOMMENDED_SERIES
                ));
        } else {
            $this->addFlash('error', 'Oops! Something went wrong and some recommended series could not be obtained.');
        }

        return $recommendedSeries;
    }
}