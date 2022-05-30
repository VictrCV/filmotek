<?php

namespace App\Controller\AppController;

use App\Controller\ApiController\SeriesListApiController;
use App\Controller\ApiController\UserApiController;
use App\Entity\SeriesList;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
class FavouritesController extends AbstractController
{
    private SeriesListApiController $seriesListApiController;

    public function __construct(SeriesListApiController $seriesListApiController)
    {
        $this->seriesListApiController = $seriesListApiController;
    }

    /**
     * @Route("/favourites", name="favourites")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function favourites(Request $request): RedirectResponse|Response
    {
        $session = $request->getSession();
        $user = $session->get(UserApiController::USER_ID);

        $request = Request::create(
            SeriesListApiController::SERIES_LIST_GET_BY_USER_ROUTE . $user,
            'GET',
            [SeriesList::TYPE_ATTR => SeriesList::FAVOURITES]
        );
        $response = $this->seriesListApiController->getByUserAction($request, $user);

        if ($response->getStatusCode() != Response::HTTP_OK) {
            $this->addFlash('error', 'Oops! Something went wrong and the favourites series could not be obtained.');
            return $this->redirectToRoute('search', []);
        }

        $favourites = json_decode($response->getContent(), true)[SeriesList::SERIES_LIST_ATTR];
        return $this->render('favourites/favourites.html.twig', [
            'favourites' => $favourites,
        ]);
    }
}