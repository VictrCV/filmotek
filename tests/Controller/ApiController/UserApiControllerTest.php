<?php

namespace App\Tests\Controller\ApiController;

use App\Controller\ApiController\UserApiController;
use App\Entity\User;
use Faker\Factory as FakerFactoryAlias;
use Faker\Generator as FakerGeneratorAlias;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserApiControllerTest extends WebTestCase
{

    private static FakerGeneratorAlias $faker;

    /**
     * Sets up the fixture.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public static function setupBeforeClass(): void
    {
        self::$faker = FakerFactoryAlias::create();
    }

    public function testPostUserAction201Created()
    {

        $data = [
            User::USERNAME_ATTR => self::$faker->userName(),
            User::PASSWORD_ATTR => self::$faker->password()
        ];

        $client = static::createClient();
        $client->request(
            'POST',
            UserApiController::USER_API_ROUTE,
            [],
            [],
            [],
            strval(json_encode($data))
        );

        $response = $client->getResponse();

        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        self::assertTrue($response->isSuccessful());
        self::assertJson(strval($response->getContent()));
        $user = json_decode(strval($response->getContent()), true);
        self::assertNotEmpty($user['user']['id']);
        self::assertSame($data[User::USERNAME_ATTR], $user['user'][User::USERNAME_ATTR]);
        self::assertNotEmpty($user['user'][User::PASSWORD_ATTR]);
    }

    public function testPostUserAction400BadRequest()
    {

        $data = [
            User::USERNAME_ATTR => self::$faker->userName(),
            User::PASSWORD_ATTR => self::$faker->password()
        ];

        $client = static::createClient();
        $client->request(
            'POST',
            UserApiController::USER_API_ROUTE,
            [],
            [],
            [],
            strval(json_encode($data))
        );
        $client->request(
            'POST',
            UserApiController::USER_API_ROUTE,
            [],
            [],
            [],
            strval(json_encode($data))
        );

        $response = $client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }
}