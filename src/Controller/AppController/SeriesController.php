<?php

namespace App\Controller\AppController;

use App\Controller\ApiController\CommentApiController;
use App\Controller\ApiController\RatingApiController;
use App\Controller\ApiController\SeriesApiController;
use App\Controller\ApiController\SeriesListApiController;
use App\Controller\ApiController\UserApiController;
use App\Entity\Comment;
use App\Entity\Rating;
use App\Entity\Series;
use App\Entity\SeriesList;
use App\Form\CommentType;
use App\Form\TemporaryMarksType;
use App\Utility\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
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
    protected RatingApiController $ratingApiController;
    protected CommentApiController $commentApiController;

    public function __construct(
        HttpClientInterface     $client,
        SeriesApiController     $seriesApiController,
        SeriesListApiController $seriesListApiController,
        RatingApiController     $ratingApiController,
        CommentApiController    $commentApiController
    )
    {
        $this->client = $client;
        $this->seriesApiController = $seriesApiController;
        $this->seriesListApiController = $seriesListApiController;
        $this->ratingApiController = $ratingApiController;
        $this->commentApiController = $commentApiController;
    }

    /**
     * @Route("{list}/series/{apiId}", name="series")
     * @param Request $request
     * @param string $list
     * @param string $apiId
     * @return RedirectResponse|Response
     */
    public function series(Request $request, string $list, string $apiId): RedirectResponse|Response
    {
        $userId = $request->getSession()->get(UserApiController::USER_ID);

        $series = $this->getSeries($apiId);
        if (!isset($series)) {
            $this->addFlash('error', 'Oops! Something went wrong and the series could not be obtained.');
            return $this->redirectToRoute($list);
        }

        if (isset($series['id'])) {
            $averageRating = $this->getAverageRating($series['id']);

            if ($userId !== null) {
                $inFavourites = $this->isSeriesInList($userId, SeriesList::FAVOURITES, $series['id']);
                $inIncompatibleList = $this->isSeriesInIncompatibleList($userId, $series['id']);

                if ($list != SearchController::SEARCH_LIST) {
                    $seriesList = $this->getSeriesList($list, $userId, $series['id']);
                    if (!isset($seriesList)) {
                        $this->addFlash('error', 'Oops! Something went wrong and the series could not be loaded.');
                        return $this->redirectToRoute($list);
                    }

                    $userRating = $this->getUserRating($userId, $series['id']);

                    $temporaryMarksForm = $this->createTemporaryMarksForm($series[Series::IS_FILM_ATTR]);
                    $temporaryMarksForm->handleRequest($request);
                    $submitTemporaryMarksForm = $this->submitTemporaryMarksForm(
                        $temporaryMarksForm,
                        $seriesList['id'],
                        $series[Series::IS_FILM_ATTR]
                    );
                    $temporaryMarksFormView = $temporaryMarksForm->createView();

                    $commentForm = $this->createForm(CommentType::class);
                    $commentForm->handleRequest($request);
                    $comment = $this->submitCommentForm($commentForm, $series['id'], $userId);

                    $commentFormView = $commentForm->createView();
                }
            }
            $comments = $this->getComments($series['id']);
        }

        if (isset($submitTemporaryMarksForm)) {
            $seriesList = $submitTemporaryMarksForm;
        }

        if (isset($comment)) {
            $commentForm = $this->createForm(CommentType::class);
            $commentFormView = $commentForm->createView();
        }

        $temporaryMarksFormView = $temporaryMarksFormView ?? null;
        $commentFormView = $commentFormView ?? null;
        $inFavourites = $inFavourites ?? false;
        $inIncompatibleList = $inIncompatibleList ?? false;
        $seriesList = $seriesList ?? null;
        $userRating = $userRating ?? null;
        $averageRating = $averageRating ?? null;
        $comments = $comments ?? null;

        return $this->render('series/series.html.twig', [
            'temporaryMarksForm' => $temporaryMarksFormView,
            'commentForm' => $commentFormView,
            'series' => $series,
            'inFavourites' => $inFavourites,
            'inIncompatibleList' => $inIncompatibleList,
            'seriesList' => $seriesList,
            'userRating' => $userRating,
            'averageRating' => $averageRating,
            'comments' => $comments
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

        return $series ?? null;
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
        $data[SeriesList::TYPE_ATTR] = match ($list) {
            'favourites' => SeriesList::FAVOURITES,
            'to_watch' => SeriesList::TO_WATCH,
            'in_progress' => SeriesList::IN_PROGRESS,
            default => $list,
        };

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


    protected function getSeries(string $apiId): ?array
    {
        $getSeriesRequest = Request::create(
            SeriesApiController::SERIES_GET_BY_API_ID_ROUTE . $apiId
        );
        $response = $this->seriesApiController->getByApiIdAction($getSeriesRequest, $apiId);

        if ($response->getStatusCode() == Response::HTTP_NOT_FOUND) {
            $series = $this->getSeriesFromRapidapi($apiId);
        } else {
            $series = json_decode($response->getContent(), true)[Series::SERIES_ATTR];
        }
        return $series;
    }

    protected function submitTemporaryMarksForm(FormInterface $temporaryMarksForm, int $seriesListId, bool $isFilm): ?array
    {
        if ($temporaryMarksForm->isSubmitted() && $temporaryMarksForm->isValid()) {
            $formData = $temporaryMarksForm->getData();
            $data = [SeriesList::TIME_ATTR => $formData[SeriesList::TIME_ATTR]->format(SeriesList::TIME_FORMAT)];

            if (!$isFilm) {
                $data[SeriesList::SEASON_ATTR] = $formData[SeriesList::SEASON_ATTR];
                $data[SeriesList::EPISODE_ATTR] = $formData[SeriesList::EPISODE_ATTR];
            }

            $request = Request::create(
                SeriesListApiController::SERIES_LIST_API_ROUTE . '/' . $seriesListId,
                'PUT',
                [], [], [], [],
                json_encode($data)
            );
            $response = $this->seriesListApiController->putAction($request, $seriesListId);

            if ($response->getStatusCode() == Response::HTTP_OK) {
                $seriesList = json_decode($response->getContent(), true)[SeriesList::SERIES_LIST_ATTR];
            } else {
                $this->addFlash('error', 'Oops! Something went wrong and it was not possible to save changes.');
            }
        }

        return $seriesList ?? null;
    }

    protected function submitCommentForm(FormInterface $commentForm, int $seriesId, int $userId): ?array
    {
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $text = $commentForm->getData()[Comment::TEXT_ATTR];

            if (preg_match(CommentApiController::TEXT_REGEX, $text)) {
                $data = [
                    Comment::TEXT_ATTR => $text,
                    Comment::SERIES_ATTR => $seriesId,
                    Comment::USER_ATTR => $userId
                ];

                $request = Request::create(
                    CommentApiController::COMMENT_API_ROUTE,
                    'POST',
                    [], [], [], [],
                    json_encode($data)
                );
                $response = $this->commentApiController->postAction($request);

                if ($response->getStatusCode() == Response::HTTP_CREATED) {
                    $comment = json_decode($response->getContent(), true)[Comment::COMMENT_ATTR];
                } else {
                    $this->addFlash('error', 'Oops! Something went wrong and it was not possible to create the comment.');
                }
            } else {
                $this->addFlash('error', 'The comment must contain at least one non-whitespace character.');
            }
        }

        return $comment ?? null;
    }

    protected function getUserRating(int $userId, int $seriesId): ?array
    {
        $request = Request::create(
            RatingApiController::RATING_GET_BY_USER_ROUTE . $userId,
            'GET',
            [Rating::SERIES_ATTR => $seriesId]
        );
        $response = $this->ratingApiController->getByUserAction($request, $userId);

        return $response->getStatusCode() == Response::HTTP_OK
            ? json_decode($response->getContent(), true)[Rating::RATING_ATTR][0]
            : null;
    }

    protected function getAverageRating(int $seriesId): ?float
    {
        $request = Request::create(
            RatingApiController::RATING_GET_AVERAGE_RATING_ROUTE . $seriesId,
            'GET'
        );
        $response = $this->ratingApiController->getAverageRatingAction($request, $seriesId);

        if ($response->getStatusCode() == Response::HTTP_OK) {
            $averageRating = json_decode($response->getContent(), true)[RatingApiController::AVERAGE_RATING];
            $roundedAverageRating = round($averageRating);
        }

        return $roundedAverageRating ?? null;
    }

    protected function createTemporaryMarksForm(bool $isFilm): FormInterface
    {
        $temporaryMarksForm = $this->createForm(TemporaryMarksType::class);
        if ($isFilm) {
            $temporaryMarksForm->remove(SeriesList::SEASON_ATTR);
            $temporaryMarksForm->remove(SeriesList::EPISODE_ATTR);
        }

        return $temporaryMarksForm;
    }

    protected function getComments(int $seriesId): ?array
    {
        $request = Request::create(
            CommentApiController::COMMENT_GET_BY_SERIES_ROUTE . $seriesId,
            'GET'
        );
        $response = $this->commentApiController->getBySeriesAction($request, $seriesId);

        if ($response->getStatusCode() == Response::HTTP_OK) {
            $comments = json_decode($response->getContent(), true)[Comment::COMMENT_ATTR];
        }

        return $comments ?? null;
    }
}