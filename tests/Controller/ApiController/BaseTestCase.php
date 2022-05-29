<?php

namespace App\Tests\Controller\ApiController;

use App\Controller\ApiController\SeriesApiController;
use App\Controller\ApiController\UserApiController;
use App\Entity\Series;
use App\Entity\User;
use Faker\Factory as FakerFactoryAlias;
use Faker\Generator as FakerGeneratorAlias;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseTestCase extends WebTestCase
{
    protected static KernelBrowser $client;
    protected static FakerGeneratorAlias $faker;

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
     * @param string $username
     * @param string $password
     * @return array<string,mixed>
     */
    protected function getAuthTokenHeader(string $username, string $password): array
    {
        $data = [
            User::USERNAME_ATTR => $username,
            User::PASSWORD_ATTR => $password
        ];

        self::$client->request(
            'POST',
            UserApiController::LOGIN_API_ROUTE,
            [], [], [],
            json_encode($data)
        );

        $response = self::$client->getResponse();
        return ['HTTP_Authorization' => $response->headers->get('Authorization')];
    }

    protected function createSeries(): array
    {
        $data = [
            Series::API_ID_ATTR => 'tt' . self::$faker->randomNumber(7),
            Series::TITLE_ATTR => self::$faker->sentence(3),
            Series::IS_FILM_ATTR => self::$faker->boolean(),
            Series::SYNOPSIS_ATTR => self::$faker->sentence(30),
            Series::IMAGE_URL_ATTR => self::$faker->imageUrl()
        ];

        self::$client->request(
            'POST',
            SeriesApiController::SERIES_API_ROUTE,
            [], [], [],
            json_encode($data)
        );
        $response = self::$client->getResponse();
        return json_decode($response->getContent(), true)[Series::SERIES_ATTR];
    }
}