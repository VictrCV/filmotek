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
     * @covers ::postActionCheckBadRequest
     * @return array
     * @throws Exception
     */
    public function testPostSeriesListAction201Created(): array
    {
        $seriesId = self::createSeries()['id'];
        $userId = self::createUser()['id'];

        $data = [
            SeriesList::TYPE_ATTR => self::$faker->randomElement([
                SeriesList::FAVOURITES,
                SeriesList::IN_PROGRESS,
                SeriesList::TO_WATCH
            ]),
            SeriesList::SERIES_ATTR => $seriesId,
            SeriesList::USER_ATTR => intval($userId),
            SeriesList::SEASON_ATTR => self::$faker->randomDigitNot(0),
            SeriesList::EPISODE_ATTR => self::$faker->numberBetween(1, 50),
            SeriesList::TIME_ATTR => self::$faker->time()
        ];

        self::$client->request(
            'POST',
            SeriesListApiController::SERIES_LIST_API_ROUTE,
            [], [], [],
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
        self::assertSame($data[SeriesList::SEASON_ATTR], $seriesList[SeriesList::SEASON_ATTR]);
        self::assertSame($data[SeriesList::EPISODE_ATTR], $seriesList[SeriesList::EPISODE_ATTR]);
        self::assertSame($data[SeriesList::TIME_ATTR], $seriesList[SeriesList::TIME_ATTR]);
        return $seriesList;
    }

    /**
     * @depends testPostSeriesListAction201Created
     * @covers ::postAction
     * @covers ::postActionCheckBadRequest
     * @return void
     * @throws Exception
     */
    public function testPostSeriesListAction400BadRequestSeriesExistsInList(array $seriesList)
    {
        $data = [
            SeriesList::TYPE_ATTR => $seriesList[SeriesList::TYPE_ATTR],
            SeriesList::SERIES_ATTR => $seriesList[SeriesList::SERIES_ATTR]['id'],
            SeriesList::USER_ATTR => $seriesList[SeriesList::USER_ATTR]['id']
        ];

        self::$client->request(
            'POST',
            SeriesListApiController::SERIES_LIST_API_ROUTE,
            [], [], [],
            json_encode($data)
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @covers ::postAction
     * @covers ::postActionCheckBadRequest
     * @return void
     * @throws Exception
     */
    public function testPostSeriesListAction400BadRequestSeriesExistsInIncompatibleList()
    {
        $seriesId = self::createSeries()['id'];
        $userId = self::createUser()['id'];

        $data = [
            SeriesList::TYPE_ATTR => SeriesList::TO_WATCH,
            SeriesList::SERIES_ATTR => $seriesId,
            SeriesList::USER_ATTR => intval($userId)
        ];

        self::$client->request(
            'POST',
            SeriesListApiController::SERIES_LIST_API_ROUTE,
            [], [], [],
            json_encode($data)
        );

        $data[SeriesList::TYPE_ATTR] = SeriesList::IN_PROGRESS;

        self::$client->request(
            'POST',
            SeriesListApiController::SERIES_LIST_API_ROUTE,
            [], [], [],
            json_encode($data)
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @covers ::postAction
     * @covers ::postActionCheckBadRequest
     * @return void
     * @throws Exception
     */
    public function testPostSeriesListAction400BadRequestSeriesNotExists()
    {
        $userId = self::createUser()['id'];

        $data = [
            SeriesList::TYPE_ATTR => self::$faker->randomElement([
                SeriesList::FAVOURITES,
                SeriesList::IN_PROGRESS,
                SeriesList::TO_WATCH
            ]),
            SeriesList::SERIES_ATTR => -1,
            SeriesList::USER_ATTR => intval($userId)
        ];

        self::$client->request(
            'POST',
            SeriesListApiController::SERIES_LIST_API_ROUTE,
            [], [], [],
            json_encode($data)
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @covers ::postAction
     * @covers ::postActionCheckBadRequest
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
            [], [], [],
            json_encode($data)
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @covers ::postAction
     * @covers ::postActionCheckBadRequest
     * @return void
     * @throws Exception
     */
    public function testPostSeriesListAction400BadRequestWrongType()
    {
        $seriesId = self::createSeries()['id'];
        $userId = self::createUser()['id'];

        $data = [
            SeriesList::TYPE_ATTR => self::$faker->lexify(),
            SeriesList::SERIES_ATTR => $seriesId,
            SeriesList::USER_ATTR => intval($userId)
        ];

        self::$client->request(
            'POST',
            SeriesListApiController::SERIES_LIST_API_ROUTE,
            [], [], [],
            json_encode($data)
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @covers ::postAction
     * @covers ::postActionCheckBadRequest
     * @return void
     * @throws Exception
     */
    public function testPostSeriesListAction422UnprocessableEntity()
    {
        self::$client->request(
            'POST',
            SeriesListApiController::SERIES_LIST_API_ROUTE
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @depends testPostSeriesListAction201Created
     * @covers ::getByUserAction
     * @return void
     * @throws Exception
     */
    public function testGetSeriesListByUserAction200Ok(array $seriesList)
    {
        self::$client->request(
            'GET',
            SeriesListApiController::SERIES_LIST_GET_BY_USER_ROUTE . $seriesList[SeriesList::USER_ATTR]['id'],
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue($response->isSuccessful());
        self::assertJson($response->getContent());
        $seriesListResponse = json_decode($response->getContent(), true)[SeriesList::SERIES_LIST_ATTR];
        self::assertEquals($seriesList[SeriesList::USER_ATTR]['id'], $seriesListResponse[0][SeriesList::USER_ATTR]['id']);
    }

    /**
     * @depends testPostSeriesListAction201Created
     * @covers ::getByUserAction
     * @return void
     * @throws Exception
     */
    public function testGetSeriesListByUserAction200OkBodyParams(array $seriesList)
    {
        $data = [
            SeriesList::TYPE_ATTR => $seriesList[SeriesList::TYPE_ATTR],
            SeriesList::SERIES_ATTR => $seriesList[SeriesList::SERIES_ATTR]['id'],
        ];

        self::$client->request(
            'GET',
            SeriesListApiController::SERIES_LIST_GET_BY_USER_ROUTE . $seriesList[SeriesList::USER_ATTR]['id'],
            $data
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue($response->isSuccessful());
        self::assertJson($response->getContent());
        $seriesListResponse = json_decode($response->getContent(), true)[SeriesList::SERIES_LIST_ATTR][0];
        self::assertEquals($seriesList[SeriesList::USER_ATTR]['id'], $seriesListResponse[SeriesList::USER_ATTR]['id']);
        self::assertEquals($seriesList[SeriesList::TYPE_ATTR], $seriesListResponse[SeriesList::TYPE_ATTR]);
        self::assertEquals($seriesList[SeriesList::SERIES_ATTR]['id'], $seriesListResponse[SeriesList::SERIES_ATTR]['id']);
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
            SeriesListApiController::SERIES_LIST_GET_BY_USER_ROUTE . -1,
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @depends testPostSeriesListAction201Created
     * @covers ::putAction
     * @return void
     * @throws Exception
     */
    public function testPutSeriesListAction200Ok(array $seriesList)
    {
        if ($seriesList[SeriesList::TYPE_ATTR] == SeriesList::FAVOURITES) {
            $type = self::$faker->randomElement([
                SeriesList::IN_PROGRESS,
                SeriesList::TO_WATCH
            ]);
        } else if ($seriesList[SeriesList::TYPE_ATTR] == SeriesList::TO_WATCH) {
            $type = self::$faker->randomElement([
                SeriesList::IN_PROGRESS,
                SeriesList::FAVOURITES
            ]);
        } else {
            $type = self::$faker->randomElement([
                SeriesList::FAVOURITES,
                SeriesList::TO_WATCH
            ]);
        }

        $seriesId = self::createSeries()['id'];
        $userId = self::createUser()['id'];

        $data = [
            SeriesList::TYPE_ATTR => $type,
            SeriesList::SERIES_ATTR => $seriesId,
            SeriesList::USER_ATTR => $userId,
            SeriesList::SEASON_ATTR => self::$faker->randomDigitNot(0),
            SeriesList::EPISODE_ATTR => self::$faker->numberBetween(1, 50),
            SeriesList::TIME_ATTR => self::$faker->time()
        ];

        self::$client->request(
            'PUT',
            SeriesListApiController::SERIES_LIST_API_ROUTE . '/' . $seriesList['id'],
            [], [], [],
            json_encode($data)
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue($response->isSuccessful());
        self::assertJson($response->getContent());
        $seriesListResponse = json_decode($response->getContent(), true)[SeriesList::SERIES_LIST_ATTR];
        self::assertEquals($type, $seriesListResponse[SeriesList::TYPE_ATTR]);
        self::assertEquals($seriesId, $seriesListResponse[SeriesList::SERIES_ATTR]['id']);
        self::assertEquals($userId, $seriesListResponse[SeriesList::USER_ATTR]['id']);
        self::assertSame($data[SeriesList::SEASON_ATTR], $seriesListResponse[SeriesList::SEASON_ATTR]);
        self::assertSame($data[SeriesList::EPISODE_ATTR], $seriesListResponse[SeriesList::EPISODE_ATTR]);
        self::assertSame($data[SeriesList::TIME_ATTR], $seriesListResponse[SeriesList::TIME_ATTR]);
    }

    /**
     * @covers ::putAction
     * @return void
     * @throws Exception
     */
    public function testPutSeriesListAction404NotFound()
    {
        self::$client->request(
            'PUT',
            SeriesListApiController::SERIES_LIST_API_ROUTE . '/-1'
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @depends testPostSeriesListAction201Created
     * @covers ::putAction
     * @return void
     * @throws Exception
     */
    public function testPutSeriesListAction400BadRequestWrongType(array $seriesList)
    {
        self::$client->request(
            'PUT',
            SeriesListApiController::SERIES_LIST_API_ROUTE . '/' . $seriesList['id'],
            [], [], [],
            json_encode([SeriesList::TYPE_ATTR => self::$faker->lexify()])
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @depends testPostSeriesListAction201Created
     * @covers ::putAction
     * @return void
     * @throws Exception
     */
    public function testPutSeriesListAction400BadRequestSeriesNotExists(array $seriesList)
    {
        self::$client->request(
            'PUT',
            SeriesListApiController::SERIES_LIST_API_ROUTE . '/' . $seriesList['id'],
            [], [], [],
            json_encode([SeriesList::SERIES_ATTR => -1])
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @depends testPostSeriesListAction201Created
     * @covers ::putAction
     * @return void
     * @throws Exception
     */
    public function testPutSeriesListAction400BadRequestUserNotExists(array $seriesList)
    {
        self::$client->request(
            'PUT',
            SeriesListApiController::SERIES_LIST_API_ROUTE . '/' . $seriesList['id'],
            [], [], [],
            json_encode([SeriesList::USER_ATTR => -1])
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @depends testPostSeriesListAction201Created
     * @covers ::deleteAction
     * @return void
     * @throws Exception
     */
    public function testDeleteSeriesListAction204NoContent(array $seriesList)
    {

        self::$client->request(
            'DELETE',
            SeriesListApiController::SERIES_LIST_API_ROUTE . '/' . $seriesList['id']
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertTrue($response->isSuccessful());
        self::assertEmpty($response->getContent());
    }

    /**
     * @covers ::deleteAction
     * @return void
     * @throws Exception
     */
    public function testDeleteSeriesListAction404NotFound()
    {

        self::$client->request(
            'DELETE',
            SeriesListApiController::SERIES_LIST_API_ROUTE . '/' . -1
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }
}