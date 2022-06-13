<?php

namespace App\Controller\AppController;

use App\Controller\ApiController\UserApiController;
use App\Entity\SeriesList;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
class ToWatchController extends SeriesListController
{

    /**
     * @Route("/to_watch", name="to_watch")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function toWatch(Request $request): RedirectResponse|Response
    {
        $session = $request->getSession();
        $user = $session->get(UserApiController::USER_ID);

        return $this->loadSeriesList($user, SeriesList::TO_WATCH);
    }
}