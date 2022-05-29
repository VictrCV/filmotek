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
 * @package App\Tests\ApiController
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

    /**
     * @covers ::optionsAction
     * @return void
     * @throws Exception
     */
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
     * @covers ::postAction
     * @return array
     * @throws Exception
     */
    public function testPostUserAction201Created(): array
    {

        $data = [
            User::USERNAME_ATTR => self::$faker->userName(),
            User::PASSWORD_ATTR => self::$faker->password()
        ];

        self::$client->request(
            'POST',
            UserApiController::USER_API_ROUTE,
            [], [], [],
            json_encode($data)
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        self::assertTrue($response->isSuccessful());
        self::assertJson($response->getContent());
        $user = json_decode($response->getContent(), true);
        self::assertNotEmpty($user[User::USER_ATTR]['id']);
        self::assertSame($data[User::USERNAME_ATTR], $user[User::USER_ATTR][User::USERNAME_ATTR]);
        self::assertArrayNotHasKey(User::PASSWORD_ATTR, $user[User::USER_ATTR]);

        return $data;
    }

    /**
     * @depends testPostUserAction201Created
     * @covers ::postAction
     * @return void
     * @throws Exception
     */
    public function testPostUserAction400BadRequest(array $data)
    {
        self::$client->request(
            'POST',
            UserApiController::USER_API_ROUTE,
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
    public function testPostUserAction422UnprocessableEntity()
    {
        self::$client->request(
            'POST',
            UserApiController::USER_API_ROUTE
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @depends testPostUserAction201Created
     * @covers ::loginAction
     * @return void
     * @throws Exception
     */
    public function testLoginUserAction200Ok(array $data)
    {

        self::$client->request(
            'POST',
            UserApiController::LOGIN_API_ROUTE,
            [], [], [],
            json_encode($data)
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue($response->isSuccessful());
        self::assertTrue($response->headers->has('Authorization'));
    }

    /**
     * @covers ::loginAction
     * @return void
     * @throws Exception
     */
    public function testLoginUserAction401Unauthorized()
    {
        $data = [
            User::USERNAME_ATTR => self::$faker->userName(),
            User::PASSWORD_ATTR => self::$faker->password()
        ];

        self::$client->request(
            'POST',
            UserApiController::LOGIN_API_ROUTE,
            [], [], [],
            json_encode($data)
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @covers ::loginAction
     * @return void
     * @throws Exception
     */
    public function testLoginUserAction422UnprocessableEntity()
    {
        $data = [
            User::PASSWORD_ATTR => self::$faker->password()
        ];

        self::$client->request(
            'POST',
            UserApiController::LOGIN_API_ROUTE,
            [], [], [],
            json_encode($data)
        );

        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }
}