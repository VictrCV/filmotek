<?php

namespace App\Tests\Controller\ApiController;

use App\Controller\ApiController\SeriesApiController;
use App\Entity\Series;
use Exception;
use Faker\Factory as FakerFactoryAlias;
use Faker\Generator as FakerGeneratorAlias;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SeriesApiControllerTest
 *
 * @package App\Tests\ApiController
 * @group   controllers
 *
 * @coversDefaultClass \App\Controller\ApiController\SeriesApiController
 */
class SeriesApiControllerTest extends WebTestCase
{
    private static KernelBrowser $client;
    private static FakerGeneratorAlias $faker;

    /**
     * Sets up the fixture.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public static function setupBeforeClass(): void
    {
        self::$client = static::createClient();
        self::$faker = FakerFactoryAlias::create();
    }

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
            SeriesApiController::SERIES_API_ROUTE
        );
        $response = self::$client->getResponse();

        self::assertSame(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode()
        );
        self::assertNotEmpty($response->headers->get('Allow'));
    }

    /**
     * Implements testPostSeriesAction201Created()
     *
     * @covers ::postAction
     * @return array
     * @throws Exception
     */
    public function testPostSeriesAction201Created(): array
    {

        $data = [
            Series::API_ID_ATTR => 'tt' . self::$faker->randomNumber(7),
            Series::TITLE_ATTR => self::$faker->sentence(3),
            Series::IS_FILM_ATTR => self::$faker->boolean(),
            Series::SYNOPSIS_ATTR => self::$faker->sentence(30),
            Series::IMAGE_URL_ATTR => self::$faker->imageUrl(),
            Series::TIME_ATTR => self::$faker->time()
        ];

        self::$client->request(
            'POST',
            SeriesApiController::SERIES_API_ROUTE,
            [], [], [],
            strval(json_encode($data))
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        self::assertTrue($response->isSuccessful());
        self::assertJson(strval($response->getContent()));
        $series = json_decode(strval($response->getContent()), true);
        self::assertNotEmpty($series[Series::SERIES_ATTR]['id']);
        self::assertSame($data[Series::API_ID_ATTR], $series[Series::SERIES_ATTR][Series::API_ID_ATTR]);
        self::assertSame($data[Series::TITLE_ATTR], $series[Series::SERIES_ATTR][Series::TITLE_ATTR]);
        self::assertSame($data[Series::IS_FILM_ATTR], $series[Series::SERIES_ATTR][Series::IS_FILM_ATTR]);
        self::assertSame($data[Series::SYNOPSIS_ATTR], $series[Series::SERIES_ATTR][Series::SYNOPSIS_ATTR]);
        self::assertSame($data[Series::IMAGE_URL_ATTR], $series[Series::SERIES_ATTR][Series::IMAGE_URL_ATTR]);

        return $data;
    }

    /**
     * Implements testPostSeriesAction400BadRequest()
     *
     * @depends testPostSeriesAction201Created
     * @covers ::postAction
     * @return void
     * @throws Exception
     */
    public function testPostSeriesAction400BadRequest(array $data)
    {
        self::$client->request(
            'POST',
            SeriesApiController::SERIES_API_ROUTE,
            [], [], [],
            strval(json_encode($data))
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * Implements testPostSeriesAction422UnprocessableEntity()
     *
     * @covers ::postAction
     * @return void
     * @throws Exception
     */
    public function testPostSeriesAction422UnprocessableEntity()
    {

        $data = [];

        self::$client->request(
            'POST',
            SeriesApiController::SERIES_API_ROUTE,
            [], [], [],
            strval(json_encode($data))
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }
}