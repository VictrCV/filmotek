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
     * @covers ::postAction
     * @return array
     * @throws Exception
     */
    public function testPostSeriesListAction201Created(): array
    {
        $seriesId = self::createSeries()['id'];

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
            json_encode($data)
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        self::assertTrue($response->isSuccessful());
        self::assertJson($response->getContent());
        $seriesList = json_decode($response->getContent(), true)[SeriesList::SERIES_LIST_ATTR];
        self::assertNotEmpty($seriesList['id']);
        self::assertSame($data[SeriesList::TYPE_ATTR], $seriesList[SeriesList::TYPE_ATTR]);
        self::assertSame($data[SeriesList::SERIES_ATTR], $seriesList[SeriesList::SERIES_ATTR]['id']);
        self::assertSame($data[SeriesList::USER_ATTR], $seriesList[SeriesList::USER_ATTR]['id']);
        return $data;
    }

    /**
     * @depends testPostSeriesListAction201Created
     * @covers ::postAction
     * @return void
     * @throws Exception
     */
    public function testPostSeriesListAction400BadRequestSeriesExistsInList(array $data)
    {
        self::$client->request(
            'POST',
            SeriesListApiController::SERIES_LIST_API_ROUTE,
            [], [], self::getAuthTokenHeader($_ENV['USER_USERNAME'], $_ENV['USER_PASSWORD']),
            json_encode($data)
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @covers ::postAction
     * @return void
     * @throws Exception
     */
    public function testPostSeriesListAction400BadRequestSeriesExistsInIncompatibleList()
    {
        $seriesId = self::createSeries()['id'];

        $data = [
            SeriesList::TYPE_ATTR => SeriesList::TO_WATCH,
            SeriesList::SERIES_ATTR => $seriesId,
            SeriesList::USER_ATTR => intval($_ENV['USER_ID'])
        ];

        self::$client->request(
            'POST',
            SeriesListApiController::SERIES_LIST_API_ROUTE,
            [], [], self::getAuthTokenHeader($_ENV['USER_USERNAME'], $_ENV['USER_PASSWORD']),
            json_encode($data)
        );

        $data[SeriesList::TYPE_ATTR] = SeriesList::IN_PROGRESS;

        self::$client->request(
            'POST',
            SeriesListApiController::SERIES_LIST_API_ROUTE,
            [], [], self::getAuthTokenHeader($_ENV['USER_USERNAME'], $_ENV['USER_PASSWORD']),
            json_encode($data)
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @covers ::postAction
     * @return void
     * @throws Exception
     */
    public function testPostSeriesListAction400BadRequestSeriesNotExists()
    {
        $data = [
            SeriesList::TYPE_ATTR => self::$faker->randomElement([
                SeriesList::FAVOURITES,
                SeriesList::IN_PROGRESS,
                SeriesList::TO_WATCH
            ]),
            SeriesList::SERIES_ATTR => -1,
            SeriesList::USER_ATTR => intval($_ENV['USER_ID'])
        ];

        self::$client->request(
            'POST',
            SeriesListApiController::SERIES_LIST_API_ROUTE,
            [], [], self::getAuthTokenHeader($_ENV['USER_USERNAME'], $_ENV['USER_PASSWORD']),
            json_encode($data)
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @covers ::postAction
     * @return void
     * @throws Exception
     */
    public function testPostSeriesListAction400BadRequestUserNotExists()
    {
        $seriesId = self::createSeries()['id'];

        $data = [
            SeriesList::TYPE_ATTR => self::$faker->randomElement([
                SeriesList::FAVOURITES,
                SeriesList::IN_PROGRESS,
                SeriesList::TO_WATCH
            ]),
            SeriesList::SERIES_ATTR => $seriesId,
            SeriesList::USER_ATTR => -1
        ];

        self::$client->request(
            'POST',
            SeriesListApiController::SERIES_LIST_API_ROUTE,
            [], [], self::getAuthTokenHeader($_ENV['USER_USERNAME'], $_ENV['USER_PASSWORD']),
            json_encode($data)
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @covers ::postAction
     * @return void
     * @throws Exception
     */
    public function testPostSeriesListAction400BadRequestWrongType()
    {
        $seriesId = self::createSeries()['id'];

        $data = [
            SeriesList::TYPE_ATTR => self::$faker->lexify(),
            SeriesList::SERIES_ATTR => $seriesId,
            SeriesList::USER_ATTR => intval($_ENV['USER_ID'])
        ];

        self::$client->request(
            'POST',
            SeriesListApiController::SERIES_LIST_API_ROUTE,
            [], [], self::getAuthTokenHeader($_ENV['USER_USERNAME'], $_ENV['USER_PASSWORD']),
            json_encode($data)
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
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
     * @covers ::postAction
     * @return void
     * @throws Exception
     */
    public function testPostSeriesListAction401Unauthorized()
    {
        self::$client->request(
            'POST',
            SeriesListApiController::SERIES_LIST_API_ROUTE
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @depends testPostSeriesListAction201Created
     * @covers ::getByUserAction
     * @return void
     * @throws Exception
     */
    public function testGetSeriesListByUserAction200Ok(array $series)
    {
        self::$client->request(
            'GET',
            SeriesListApiController::SERIES_LIST_GET_BY_USER_ROUTE . $series[SeriesList::USER_ATTR],
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue($response->isSuccessful());
        self::assertJson($response->getContent());
        $seriesList = json_decode($response->getContent(), true)[SeriesList::SERIES_LIST_ATTR];
        self::assertEquals($series[SeriesList::USER_ATTR], $seriesList[0][SeriesList::USER_ATTR]['id']);
    }

    /**
     * @depends testPostSeriesListAction201Created
     * @covers ::getByUserAction
     * @return void
     * @throws Exception
     */
    public function testGetSeriesListByUserAction200OkBodyParams(array $series)
    {
        $data = [
            SeriesList::TYPE_ATTR => $series[SeriesList::TYPE_ATTR],
            SeriesList::SERIES_ATTR => $series[SeriesList::SERIES_ATTR],
        ];

        self::$client->request(
            'GET',
            SeriesListApiController::SERIES_LIST_GET_BY_USER_ROUTE . $series[SeriesList::USER_ATTR],
            $data
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue($response->isSuccessful());
        self::assertJson($response->getContent());
        $seriesList = json_decode($response->getContent(), true)[SeriesList::SERIES_LIST_ATTR][0];
        self::assertEquals($series[SeriesList::USER_ATTR], $seriesList[SeriesList::USER_ATTR]['id']);
        self::assertEquals($series[SeriesList::TYPE_ATTR], $seriesList[SeriesList::TYPE_ATTR]);
        self::assertEquals($series[SeriesList::SERIES_ATTR], $seriesList[SeriesList::SERIES_ATTR]['id']);
    }

    /**
     * @covers ::getByUserAction
     * @return void
     * @throws Exception
     */
    public function testGetSeriesListByUserAction404NotFound()
    {
        self::$client->request(
            'GET',
            SeriesListApiController::SERIES_LIST_GET_BY_USER_ROUTE . intval(-1),
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }
}