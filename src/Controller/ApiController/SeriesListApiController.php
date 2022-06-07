<?php

namespace App\Controller\ApiController;

use App\Entity\Series;
use App\Entity\SeriesList;
use App\Entity\User;
use App\Utility\Utils;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SeriesListApiController
 * @package App\Controller\ApiController
 * @Route(path=SeriesListApiController::SERIES_LIST_API_ROUTE, name="series_list_api_")
 */
class SeriesListApiController extends AbstractController
{
    public const SERIES_LIST_API_ROUTE = '/api/v1/series-list';
    public const SERIES_LIST_GET_BY_USER_ROUTE = self::SERIES_LIST_API_ROUTE . '/user/';

    public const SERIES_LIST_NOT_FOUND_MESSAGE = 'Series list not found.';

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

        if (!isset($data[SeriesList::TYPE_ATTR], $data[SeriesList::SERIES_ATTR], $data[SeriesList::USER_ATTR])) {
            return Utils::errorMessage(Response::HTTP_UNPROCESSABLE_ENTITY, "Missing data.");
        }

        $seriesList = new SeriesList();

        if (isset($data[SeriesList::SEASON_ATTR])) {
            $seriesList->setSeason($data[SeriesList::SEASON_ATTR]);
        }

        if (isset($data[SeriesList::EPISODE_ATTR])) {
            $seriesList->setEpisode($data[SeriesList::EPISODE_ATTR]);
        }

        if (isset($data[SeriesList::TIME_ATTR])) {
            $seriesList->setTime(DateTime::createFromFormat("H:i:s", $data[SeriesList::TIME_ATTR]));
        }

        try {
            $seriesList->setType($data[SeriesList::TYPE_ATTR]);

            $series = $this->entityManager
                ->getRepository(Series::class)
                ->find($data[SeriesList::SERIES_ATTR]);

            $user = $this->entityManager
                ->getRepository(User::class)
                ->find($data[SeriesList::USER_ATTR]);

            $badRequest = $this->postActionCheckBadRequest($seriesList->getType(), $series, $user);
        } catch (InvalidArgumentException) {
            $badRequest = Utils::errorMessage(Response::HTTP_BAD_REQUEST, "Wrong type.");
        }

        if (isset($badRequest)) {
            return $badRequest;
        }

        $seriesList->setSeries($series);
        $seriesList->setUser($user);

        $this->entityManager->persist($seriesList);
        $this->entityManager->flush();

        return Utils::apiResponse(
            Response::HTTP_CREATED,
            [SeriesList::SERIES_LIST_ATTR => $seriesList]
        );

    }

    /**
     * @param string $type
     * @param Series|null $series
     * @param User|null $user
     * @return Response|null
     */
    private function postActionCheckBadRequest(string $type, ?Series $series, ?User $user): ?Response
    {
        if ($series == null) {
            $badRequest = Utils::errorMessage(Response::HTTP_BAD_REQUEST, "Series does not exist.");
        } elseif ($user == null) {
            $badRequest = Utils::errorMessage(Response::HTTP_BAD_REQUEST, "User does not exist.");
        } else {
            $seriesExistsInList = $this->entityManager
                ->getRepository(SeriesList::class)
                ->findBy([
                    'type' => $type,
                    'series' => $series,
                    'user' => $user
                ]);

            if (!empty($seriesExistsInList)) {
                $badRequest = Utils::errorMessage(Response::HTTP_BAD_REQUEST,
                    "Series already exists in " . $type . " list.");
            }

            if ($type == SeriesList::TO_WATCH) {
                $incompatibleType = SeriesList::IN_PROGRESS;
            } else if ($type == SeriesList::IN_PROGRESS) {
                $incompatibleType = SeriesList::TO_WATCH;
            }

            if (isset($incompatibleType)) {
                $seriesExistsInList = $this->entityManager
                    ->getRepository(SeriesList::class)
                    ->findBy([
                        'type' => $incompatibleType,
                        'series' => $series,
                        'user' => $user
                    ]);

                if (!empty($seriesExistsInList)) {
                    $badRequest = Utils::errorMessage(Response::HTTP_BAD_REQUEST,
                        "Series exists in " . $incompatibleType . " list and cannot be in " . $type . " list too.");
                }
            }
        }

        return $badRequest ?? null;
    }

    /**
     * @return Response
     * @Route(path="", name="options", methods={"OPTIONS"})
     */
    public function optionsAction(): Response
    {
        $methods = ['OPTIONS', 'POST', 'GET', 'PUT', 'DELETE'];

        return new Response(
            null,
            Response::HTTP_NO_CONTENT,
            [
                self::HEADER_ALLOW => implode(', ', $methods),
                self::HEADER_CACHE_CONTROL => 'public, inmutable'
            ]
        );
    }

    /**
     * @param Request $request
     * @param int $userId
     * @return Response
     * @Route(path="/user/{userId}", name="getByUser", methods={"GET"})
     */
    public function getByUserAction(Request $request, int $userId): Response
    {
        $params = $request->query;
        $query = $this->entityManager
            ->getRepository(SeriesList::class)
            ->createQueryBuilder('sl')
            ->where('sl.user = :user')
            ->setParameter('user', $userId);

        if ($params->get(SeriesList::TYPE_ATTR) !== null) {
            $query = $query
                ->andWhere('sl.type = :type')
                ->setParameter('type', $params->get(SeriesList::TYPE_ATTR));
        }

        if ($params->get(SeriesList::SERIES_ATTR) !== null) {
            $query = $query
                ->andWhere('sl.series = :series')
                ->setParameter('series', $params->get(SeriesList::SERIES_ATTR));
        }

        $seriesList = $query
            ->getQuery()
            ->execute();

        if (empty($seriesList)) {
            return Utils::errorMessage(Response::HTTP_NOT_FOUND, self::SERIES_LIST_NOT_FOUND_MESSAGE);
        }

        return Utils::apiResponse(
            Response::HTTP_OK,
            [SeriesList::SERIES_LIST_ATTR => $seriesList]
        );
    }

    /**
     * @param Request $request
     * @param int $seriesListId
     * @return Response
     * @Route(path="/{seriesListId}", name="put", methods={"PUT"})
     */
    public function putAction(Request $request, int $seriesListId): Response
    {
        $seriesList = $this->entityManager
            ->getRepository(SeriesList::class)
            ->find($seriesListId);

        if (!isset($seriesList)) {
            return Utils::errorMessage(Response::HTTP_NOT_FOUND, self::SERIES_LIST_NOT_FOUND_MESSAGE);
        }

        $body = $request->getContent();
        $data = json_decode($body, true);

        if (isset($data[SeriesList::TYPE_ATTR])) {
            try {
                $seriesList->setType($data[SeriesList::TYPE_ATTR]);
            } catch (InvalidArgumentException) {
                $badRequest = Utils::errorMessage(Response::HTTP_BAD_REQUEST, "Wrong type.");
            }
        }

        if (isset($data[SeriesList::SERIES_ATTR])) {
            $series = $this->entityManager
                ->getRepository(Series::class)
                ->find($data[SeriesList::SERIES_ATTR]);
            if (!isset($series)) {
                $badRequest = Utils::errorMessage(Response::HTTP_BAD_REQUEST, "Series does not exist.");
            }
            $seriesList->setSeries($series);
        }

        if (isset($data[SeriesList::USER_ATTR])) {
            $user = $this->entityManager
                ->getRepository(User::class)
                ->find($data[SeriesList::USER_ATTR]);
            if (!isset($user)) {
                $badRequest = Utils::errorMessage(Response::HTTP_BAD_REQUEST, "User does not exist.");
            }
            $seriesList->setUser($user);
        }

        if (isset($badRequest)) {
            return $badRequest;
        }

        if (isset($data[SeriesList::SEASON_ATTR])) {
            $seriesList->setSeason($data[SeriesList::SEASON_ATTR]);
        }

        if (isset($data[SeriesList::EPISODE_ATTR])) {
            $seriesList->setEpisode($data[SeriesList::EPISODE_ATTR]);
        }

        if (isset($data[SeriesList::TIME_ATTR])) {
            $seriesList->setTime(DateTime::createFromFormat("H:i:s", $data[SeriesList::TIME_ATTR]));
        }

        $this->entityManager->flush();

        return Utils::apiResponse(
            Response::HTTP_OK,
            [SeriesList::SERIES_LIST_ATTR => $seriesList]
        );
    }

    /**
     * @param Request $request
     * @param int $seriesListId
     * @return Response
     * @Route(path="/{seriesListId}", name="delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, int $seriesListId): Response
    {
        $seriesList = $this->entityManager
            ->getRepository(SeriesList::class)
            ->find($seriesListId);

        if (!isset($seriesList)) {
            return Utils::errorMessage(Response::HTTP_NOT_FOUND, self::SERIES_LIST_NOT_FOUND_MESSAGE);
        }

        $this->entityManager->remove($seriesList);
        $this->entityManager->flush();

        return Utils::apiResponse(Response::HTTP_NO_CONTENT);
    }
}