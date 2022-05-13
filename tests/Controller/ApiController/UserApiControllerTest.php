<?php

namespace App\Tests\Controller\ApiController;

use App\Controller\ApiController\UserApiController;
use App\Entity\User;
use Exception;
use Faker\Factory as FakerFactoryAlias;
use Faker\Generator as FakerGeneratorAlias;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserApiControllerTest
 *
 * @package App\Tests\Controller\ApiController
 * @group   controllers
 *
 * @coversDefaultClass \App\Controller\ApiController\UserApiController
 */
class UserApiControllerTest extends WebTestCase
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

    public function testOptionsAction204NoContent(): void
    {

        self::$client->request(
            'OPTIONS',
            UserApiController::USER_API_ROUTE
        );
        $response = self::$client->getResponse();

        self::assertSame(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode()
        );
        self::assertNotEmpty($response->headers->get('Allow'));
    }

    /**
     * Implements testPostUserAction201Created()
     *
     * @covers ::postAction
     * @return void
     * @throws Exception
     */
    public function testPostUserAction201Created()
    {

        $data = [
            User::USERNAME_ATTR => self::$faker->userName(),
            User::PASSWORD_ATTR => self::$faker->password()
        ];

        self::$client->request(
            'POST',
            UserApiController::USER_API_ROUTE,
            [],
            [],
            [],
            strval(json_encode($data))
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        self::assertTrue($response->isSuccessful());
        self::assertJson(strval($response->getContent()));
        $user = json_decode(strval($response->getContent()), true);
        self::assertNotEmpty($user['user']['id']);
        self::assertSame($data[User::USERNAME_ATTR], $user['user'][User::USERNAME_ATTR]);
        self::assertNotEmpty($user['user'][User::PASSWORD_ATTR]);
    }

    /**
     * Implements testPostUserAction400BadRequest()
     *
     * @covers ::postAction
     * @return void
     * @throws Exception
     */
    public function testPostUserAction400BadRequest()
    {

        $data = [
            User::USERNAME_ATTR => self::$faker->userName(),
            User::PASSWORD_ATTR => self::$faker->password()
        ];

        self::$client->request(
            'POST',
            UserApiController::USER_API_ROUTE,
            [],
            [],
            [],
            strval(json_encode($data))
        );
        self::$client->request(
            'POST',
            UserApiController::USER_API_ROUTE,
            [],
            [],
            [],
            strval(json_encode($data))
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }
}