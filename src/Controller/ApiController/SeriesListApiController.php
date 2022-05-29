<?php

namespace App\Controller\ApiController;

use App\Entity\Series;
use App\Entity\SeriesList;
use App\Entity\User;
use App\Utility\Utils;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Security(
     *     expression="is_granted('IS_AUTHENTICATED_FULLY')",
     *     statusCode=401,
     *     message="`Unauthorized`: Invalid credentials."
     * )
     */
    public function postAction(Request $request): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        if (!isset($data[SeriesList::TYPE_ATTR], $data[SeriesList::SERIES_ATTR], $data[SeriesList::USER_ATTR])) {
            return Utils::errorMessage(Response::HTTP_UNPROCESSABLE_ENTITY, "Missing data.");
        }

        $seriesList = new SeriesList();

        try {
            $seriesList->setType($data[SeriesList::TYPE_ATTR]);
        } catch (InvalidArgumentException $e) {
            $badRequest = Utils::errorMessage(Response::HTTP_BAD_REQUEST, "Wrong type.");
        }

        $series = $this->entityManager
            ->getRepository(Series::class)
            ->find(intval($data[SeriesList::SERIES_ATTR]));

        $user = $this->entityManager
            ->getRepository(User::class)
            ->find(intval($data[SeriesList::USER_ATTR]));

        if (!isset($badRequest) && $series == null) {
            $badRequest = Utils::errorMessage(Response::HTTP_BAD_REQUEST, "Series does not exist.");
        } elseif ($user == null) {
            $badRequest = Utils::errorMessage(Response::HTTP_BAD_REQUEST, "User does not exist.");
        } else {
            $seriesList->setSeries($series);
            $seriesList->setUser($user);

            $seriesExistsInList = $this->entityManager
                ->getRepository(SeriesList::class)
                ->createQueryBuilder('sl')
                ->where('sl.type = :type', 'sl.series = :series', 'sl.user = :user')
                ->setParameters([
                    'type' => $seriesList->getType(),
                    'series' => $seriesList->getSeries(),
                    'user' => $seriesList->getUser()
                ])
                ->getQuery()
                ->execute();

            if (!empty($seriesExistsInList)) {
                $badRequest = Utils::errorMessage(Response::HTTP_BAD_REQUEST,
                    "Series already exists in " . $seriesList->getType() . " list.");
            }
        }

        if (isset($badRequest)) {
            return $badRequest;
        }

        $this->entityManager->persist($seriesList);
        $this->entityManager->flush();

        return Utils::apiResponse(
            Response::HTTP_CREATED,
            [SeriesList::SERIES_LIST_ATTR => $seriesList]
        );

    }

    /**
     * @return Response
     * @Route(path="", name="options", methods={"OPTIONS"})
     */
    public function optionsAction(): Response
    {
        $methods = ['POST', 'GET'];
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

    /**
     * @param Request $request
     * @param string $user
     * @return Response
     * @Route(path="/user/{user}", name="getByUser", methods={"GET"})
     */
    public function getByUserAction(Request $request, string $user): Response
    {
        $params = $request->query;

        $query = $this->entityManager
            ->getRepository(SeriesList::class)
            ->createQueryBuilder('sl')
            ->where('sl.user = :user')
            ->setParameter('user', $user);

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
            return Utils::errorMessage(Response::HTTP_NOT_FOUND, 'Series list not found.');
        }

        return Utils::apiResponse(
            Response::HTTP_OK,
            [SeriesList::SERIES_LIST_ATTR => $seriesList]
        );
    }
}