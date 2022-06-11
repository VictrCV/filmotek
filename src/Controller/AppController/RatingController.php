<?php

namespace App\Controller\AppController;

use App\Controller\ApiController\RatingApiController;
use App\Controller\ApiController\UserApiController;
use App\Entity\Rating;
use App\Entity\Series;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
class RatingController extends AbstractController
{
    protected RatingApiController $ratingApiController;

    public function __construct(RatingApiController $ratingApiController,)
    {
        $this->ratingApiController = $ratingApiController;
    }

    /**
     * @Route("{list}/rating/save/{apiId}", name="rating_save")
     * @param Request $request
     * @param string $list
     * @param string $apiId
     * @return RedirectResponse|Response
     */
    public function saveRating(Request $request, string $list, string $apiId): RedirectResponse|Response
    {
        $value = $_POST["userStar"];
        $seriesId = $_POST["seriesId"];
        $userId = $request->getSession()->get(UserApiController::USER_ID);

        $request = Request::create(
            RatingApiController::RATING_GET_BY_USER_ROUTE . $userId,
            'GET',
            [Rating::SERIES_ATTR => $seriesId]
        );
        $response = $this->ratingApiController->getByUserAction($request, $userId);

        if ($response->getStatusCode() == Response::HTTP_NOT_FOUND) {
            $data = [
                Rating::VALUE_ATTR => $value,
                Rating::SERIES_ATTR => $seriesId,
                Rating::USER_ATTR => $userId
            ];

            $request = Request::create(
                RatingApiController::RATING_API_ROUTE,
                'POST',
                [], [], [], [],
                json_encode($data)
            );
            $response = $this->ratingApiController->postAction($request);

            if ($response->getStatusCode() != Response::HTTP_CREATED) {
                $this->addFlash('error', 'Oops! Something went wrong and the rating could not be created.');
            }
        } elseif ($response->getStatusCode() == Response::HTTP_OK) {
            $ratingId = json_decode($response->getContent(), true)[Rating::RATING_ATTR][0]['id'];

            $request = Request::create(
                RatingApiController::RATING_API_ROUTE . '/' . $ratingId,
                'PUT',
                [], [], [], [],
                json_encode([Rating::VALUE_ATTR => $value])
            );
            $response = $this->ratingApiController->putAction($request, $ratingId);

            if ($response->getStatusCode() != Response::HTTP_OK) {
                $this->addFlash('error', 'Oops! Something went wrong and the rating could not be updated.');
            }
        } else {
            $this->addFlash('error', 'Oops! Something went wrong and the rating could not be saved.');
        }

        return $this->redirectToRoute('series', [
            'list' => $list,
            Series::API_ID_ATTR => $apiId
        ]);
    }
}