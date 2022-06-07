<?php

namespace App\Tests\Controller\ApiController;

use App\Controller\ApiController\RatingApiController;
use App\Entity\Rating;
use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package App\Tests\ApiController
 * @group   controllers
 *
 * @coversDefaultClass \App\Controller\ApiController\RatingApiController
 */
class RatingApiControllerTest extends BaseTestCase
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
            RatingApiController::RATING_API_ROUTE
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
    public function testPostRatingAction201Created(): array
    {
        $seriesId = self::createSeries()['id'];
        $userId = self::createUser()['id'];

        $data = [
            Rating::VALUE_ATTR => self::$faker->numberBetween(1, 5),
            Rating::SERIES_ATTR => $seriesId,
            Rating::USER_ATTR => $userId
        ];

        self::$client->request(
            'POST',
            RatingApiController::RATING_API_ROUTE,
            [], [], [],
            json_encode($data)
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        self::assertTrue($response->isSuccessful());
        self::assertJson($response->getContent());
        $rating = json_decode($response->getContent(), true)[Rating::RATING_ATTR];
        self::assertNotEmpty($rating['id']);
        self::assertEquals($data[Rating::VALUE_ATTR], $rating[Rating::VALUE_ATTR]);
        self::assertEquals($data[Rating::SERIES_ATTR], $rating[Rating::SERIES_ATTR]['id']);
        self::assertEquals($data[Rating::USER_ATTR], $rating[Rating::USER_ATTR]['id']);
        return $rating;
    }

    /**
     * @covers ::postAction
     * @return void
     * @throws Exception
     */
    public function testPostRatingAction422UnprocessableEntity()
    {
        self::$client->request(
            'POST',
            RatingApiController::RATING_API_ROUTE
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
    public function testPostRatingAction400BadRequestSeriesNotExists()
    {
        $userId = self::createUser()['id'];

        $data = [
            Rating::VALUE_ATTR => self::$faker->numberBetween(1, 5),
            Rating::SERIES_ATTR => -1,
            Rating::USER_ATTR => $userId
        ];

        self::$client->request(
            'POST',
            RatingApiController::RATING_API_ROUTE,
            [], [], [],
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
    public function testPostRatingAction400BadRequestUserNotExists()
    {
        $seriesId = self::createSeries()['id'];

        $data = [
            Rating::VALUE_ATTR => self::$faker->numberBetween(1, 5),
            Rating::SERIES_ATTR => $seriesId,
            Rating::USER_ATTR => -1
        ];

        self::$client->request(
            'POST',
            RatingApiController::RATING_API_ROUTE,
            [], [], [],
            json_encode($data)
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @depends testPostRatingAction201Created
     * @covers ::postAction
     * @return void
     * @throws Exception
     */
    public function testPostRatingAction400BadRequestRatingExists(array $rating)
    {
        $data = [
            Rating::VALUE_ATTR => self::$faker->numberBetween(1, 5),
            Rating::SERIES_ATTR => $rating[Rating::SERIES_ATTR]['id'],
            Rating::USER_ATTR => $rating[Rating::USER_ATTR]['id']
        ];

        self::$client->request(
            'POST',
            RatingApiController::RATING_API_ROUTE,
            [], [], [],
            json_encode($data)
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }
}