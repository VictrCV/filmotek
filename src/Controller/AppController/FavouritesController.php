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
class FavouritesController extends SeriesListController
{

    /**
     * @Route("/favourites", name="favourites")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function favourites(Request $request): RedirectResponse|Response
    {
        $session = $request->getSession();
        $user = $session->get(UserApiController::USER_ID);

        return $this->loadSeriesList($user, SeriesList::FAVOURITES);
    }
}