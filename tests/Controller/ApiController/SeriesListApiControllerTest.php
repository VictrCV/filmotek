<?php

namespace App\Tests\Controller\ApiController;

use App\Controller\ApiController\SeriesListApiController;
use App\Entity\SeriesList;
use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SeriesListApiControllerTest
 *
 * @package App\Tests\ApiController
 * @group   controllers
 *
 * @coversDefaultClass \App\Controller\ApiController\SeriesListApiController
 */
class SeriesListApiControllerTest extends BaseTestCase
{

    /**
     * Implements testOptionsAction204NoContent()
     *
     * @covers ::optionsAction
     * @return void
     * @throws Exception
     */
    public function testOptionsAction204NoContent(): void
    {

        self::$client->request(
            'OPTIONS',
            SeriesListApiController::SERIES_LIST_API_ROUTE
        );
        $response = self::$client->getResponse();

        self::assertSame(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode()
        );
        self::assertNotEmpty($response->headers->get('Allow'));
    }

    /**
     * Implements testPostSeriesListAction201Created()
     *
     * @covers ::postAction
     * @return array
     * @throws Exception
     */
    public function testPostSeriesListAction201Created(): array
    {
        $seriesId = self::createSeries();

        $data = [
            SeriesList::TYPE_ATTR => self::$faker->randomElement([
                SeriesList::FAVOURITES,
                SeriesList::IN_PROGRESS,
                SeriesList::TO_WATCH
            ]),
            SeriesList::SERIES_ATTR => $seriesId,
            SeriesList::USER_ATTR => intval($_ENV['USER_ID'])
        ];

        self::$client->request(
            'POST',
            SeriesListApiController::SERIES_LIST_API_ROUTE,
            [], [],
            self::getAuthTokenHeader($_ENV['USER_USERNAME'], $_ENV['USER_PASSWORD']),
            strval(json_encode($data))
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        self::assertTrue($response->isSuccessful());
        self::assertJson(strval($response->getContent()));
        $seriesList = json_decode(strval($response->getContent()), true);
        self::assertNotEmpty($seriesList[SeriesList::SERIES_LIST_ATTR]['id']);
        self::assertSame($data[SeriesList::TYPE_ATTR], $seriesList[SeriesList::SERIES_LIST_ATTR][SeriesList::TYPE_ATTR]);
        self::assertSame($data[SeriesList::SERIES_ATTR], $seriesList[SeriesList::SERIES_LIST_ATTR][SeriesList::SERIES_ATTR]['id']);
        self::assertSame($data[SeriesList::USER_ATTR], $seriesList[SeriesList::SERIES_LIST_ATTR][SeriesList::USER_ATTR]['id']);
        return $data;
    }

    /**
     * Implements testPostSeriesListAction400BadRequest()
     *
     * @depends testPostSeriesListAction201Created
     * @covers ::postAction
     * @return void
     * @throws Exception
     */
    public function testPostSeriesListAction400BadRequest(array $data)
    {
        self::$client->request(
            'POST',
            SeriesListApiController::SERIES_LIST_API_ROUTE,
            [], [], self::getAuthTokenHeader($_ENV['USER_USERNAME'], $_ENV['USER_PASSWORD']),
            strval(json_encode($data))
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * Implements testPostSeriesListAction422UnprocessableEntity()
     *
     * @covers ::postAction
     * @return void
     * @throws Exception
     */
    public function testPostSeriesListAction422UnprocessableEntity()
    {
        self::$client->request(
            'POST',
            SeriesListApiController::SERIES_LIST_API_ROUTE,
            [], [],
            self::getAuthTokenHeader($_ENV['USER_USERNAME'], $_ENV['USER_PASSWORD'])
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * Implements testPostSeriesListAction401Unauthorized()
     *
     * @covers ::postAction
     * @return void
     * @throws Exception
     */
    public function testPostSeriesListAction401Unauthorized()
    {self::$client->request(
            'POST',
            SeriesListApiController::SERIES_LIST_API_ROUTE
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }
}