<?php

namespace App\Controller\ApiController;

use App\Entity\Rating;
use App\Entity\Series;
use App\Entity\User;
use App\Utility\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @package App\Controller\ApiController
 * @Route(path=RatingApiController::RATING_API_ROUTE, name="rating_api_")
 */
class RatingApiController extends AbstractController
{
    public const RATING_API_ROUTE = '/api/v1/rating';

    private const HEADER_CACHE_CONTROL = 'Cache-Control';
    private const HEADER_ALLOW = 'Allow';

    private EntityManagerInterface $entityManager;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(path="", name="post", methods={"POST"})
     */
    public function postAction(Request $request): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        if (!isset($data[Rating::VALUE_ATTR], $data[Rating::SERIES_ATTR], $data[Rating::USER_ATTR])) {
            return Utils::errorMessage(Response::HTTP_UNPROCESSABLE_ENTITY, "Missing data.");
        }

        $series = $this->entityManager
            ->getRepository(Series::class)
            ->find($data[Rating::SERIES_ATTR]);

        if (!isset($series)) {
            $badRequest = Utils::errorMessage(Response::HTTP_BAD_REQUEST, "Series does not exist.");
        }

        $user = $this->entityManager
            ->getRepository(User::class)
            ->find($data[Rating::USER_ATTR]);

        if (!isset($user)) {
            $badRequest = Utils::errorMessage(Response::HTTP_BAD_REQUEST, "User does not exist.");
        }

        $ratingExists = $this->entityManager
            ->getRepository(Rating::class)
            ->findBy([
                'series' => $series,
                'user' => $user
            ]);

        if (!empty($ratingExists)) {
            $badRequest = Utils::errorMessage(Response::HTTP_BAD_REQUEST,
                "Rating already exists.");
        }

        if (isset($badRequest)) {
            return $badRequest;
        }

        $rating = new Rating();
        $rating->setValue($data[Rating::VALUE_ATTR]);
        $rating->setSeries($series);
        $rating->setUser($user);

        $this->entityManager->persist($rating);
        $this->entityManager->flush();

        return Utils::apiResponse(
            Response::HTTP_CREATED,
            [Rating::RATING_ATTR => $rating]
        );
    }

    /**
     * @return Response
     * @Route(path="", name="options", methods={"OPTIONS"})
     */
    public function optionsAction(): Response
    {
        $methods = ['OPTIONS', 'POST'];

        return new Response(
            null,
            Response::HTTP_NO_CONTENT,
            [
                self::HEADER_ALLOW => implode(', ', $methods),
                self::HEADER_CACHE_CONTROL => 'public, inmutable'
            ]
        );
    }
}