<?php

namespace App\Controller\ApiController;

use App\Entity\Series;
use App\Entity\SeriesList;
use App\Entity\User;
use App\Utility\Utils;
use Doctrine\ORM\EntityManagerInterface;
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
        $seriesList->setType($data[SeriesList::TYPE_ATTR]);

        $series = $this->entityManager
            ->getRepository(Series::class)
            ->find(intval($data[SeriesList::SERIES_ATTR]));

        if ($series == null) {
            return Utils::errorMessage(Response::HTTP_BAD_REQUEST, "Series doesn't exist.");
        }

        $user = $this->entityManager
            ->getRepository(User::class)
            ->find(intval($data[SeriesList::USER_ATTR]));

        if ($user == null) {
            return Utils::errorMessage(Response::HTTP_BAD_REQUEST, "User doesn't exist.");
        }

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
            return Utils::errorMessage(Response::HTTP_BAD_REQUEST,
                "Series already exists in " . $seriesList->getType() . " list.");
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