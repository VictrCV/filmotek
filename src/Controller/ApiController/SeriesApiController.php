<?php

namespace App\Controller\ApiController;

use App\Entity\Series;
use App\Utility\Utils;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SeriesApiController
 * @package App\Controller\ApiController
 * @Route(path=SeriesApiController::SERIES_API_ROUTE, name="series_api_")
 */
class SeriesApiController extends AbstractController
{
    public const SERIES_API_ROUTE = '/api/v1/series';

    private const HEADER_CACHE_CONTROL = 'Cache-Control';
    private const HEADER_ALLOW = 'Allow';

    private EntityManagerInterface $entityManager;

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

        if (!isset($data[Series::API_ID_ATTR], $data[Series::TITLE_ATTR], $data[Series::IS_FILM_ATTR],
            $data[Series::SYNOPSIS_ATTR], $data[Series::IMAGE_URL_ATTR])) {
            return Utils::errorMessage(Response::HTTP_UNPROCESSABLE_ENTITY, "Missing data.");
        }

        $series = new Series();
        $series->setApiId($data[Series::API_ID_ATTR]);
        $series->setTitle($data[Series::TITLE_ATTR]);
        $series->setIsFilm($data[Series::IS_FILM_ATTR]);
        $series->setSynopsis($data[Series::SYNOPSIS_ATTR]);
        $series->setImageUrl($data[Series::IMAGE_URL_ATTR]);

        if(isset($data[Series::SEASON_ATTR], $data[Series::EPISODE_ATTR], $data[Series::TIME_ATTR])) {
            $series->setSeason($data[Series::SEASON_ATTR]);
            $series->setEpisode($data[Series::EPISODE_ATTR]);
            $series->setTime(DateTime::createFromFormat("H:i:s", $data[Series::TIME_ATTR]));
        }

        $seriesExists = $this->entityManager
            ->getRepository(Series::class)
            ->findOneBy([Series::API_ID_ATTR => $series->getApiId()]);

        if ($seriesExists !== null) {
            return Utils::errorMessage(Response::HTTP_BAD_REQUEST, "Series already exists.");
        } else {
            $this->entityManager->persist($series);
            $this->entityManager->flush();

            return Utils::apiResponse(
                Response::HTTP_CREATED,
                [Series::SERIES_ATTR => $series]
            );
        }
    }

    /**
     * @return Response
     * @Route(path="", name="options", methods={"OPTIONS"})
     */
    public function optionsAction(): Response
    {
        $methods = ['POST'];
        $methods[] = 'OPTIONS';

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