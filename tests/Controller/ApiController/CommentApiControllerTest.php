<?php

namespace App\Tests\Controller\ApiController;

use App\Controller\ApiController\CommentApiController;
use App\Entity\Comment;
use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package App\Tests\ApiController
 * @group   controllers
 *
 * @coversDefaultClass \App\Controller\ApiController\CommentApiController
 */
class CommentApiControllerTest extends BaseTestCase
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
            CommentApiController::COMMENT_API_ROUTE
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
    public function testPostCommentAction201Created(): array
    {
        $seriesId = self::createSeries()['id'];
        $userId = self::createUser()['id'];

        $data = [
            Comment::TEXT_ATTR => self::$faker->text(),
            Comment::SERIES_ATTR => $seriesId,
            Comment::USER_ATTR => $userId
        ];

        self::$client->request(
            'POST',
            CommentApiController::COMMENT_API_ROUTE,
            [], [], [],
            json_encode($data)
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        self::assertTrue($response->isSuccessful());
        self::assertJson($response->getContent());
        $comment = json_decode($response->getContent(), true)[Comment::COMMENT_ATTR];
        self::assertNotEmpty($comment['id']);
        self::assertEquals($data[Comment::TEXT_ATTR], $comment[Comment::TEXT_ATTR]);
        self::assertNotEmpty($comment[Comment::DATETIME_ATTR]);
        self::assertEquals($data[Comment::SERIES_ATTR], $comment[Comment::SERIES_ATTR]['id']);
        self::assertEquals($data[Comment::USER_ATTR], $comment[Comment::USER_ATTR]['id']);
        return $comment;
    }

    /**
     * @covers ::postAction
     * @return void
     * @throws Exception
     */
    public function testPostCommentAction422UnprocessableEntity()
    {
        self::$client->request(
            'POST',
            CommentApiController::COMMENT_API_ROUTE
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
    public function testPostCommentAction400BadRequestSeriesNotExists()
    {
        $userId = self::createUser()['id'];

        $data = [
            Comment::TEXT_ATTR => self::$faker->sentences(),
            Comment::SERIES_ATTR => -1,
            Comment::USER_ATTR => $userId
        ];

        self::$client->request(
            'POST',
            CommentApiController::COMMENT_API_ROUTE,
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
    public function testPostCommentAction400BadRequestUserNotExists()
    {
        $seriesId = self::createSeries()['id'];

        $data = [
            Comment::TEXT_ATTR => self::$faker->sentences(),
            Comment::SERIES_ATTR => $seriesId,
            Comment::USER_ATTR => -1
        ];

        self::$client->request(
            'POST',
            CommentApiController::COMMENT_API_ROUTE,
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
    public function testPostCommentAction400BadRequestInvalidText()
    {
        $seriesId = self::createSeries()['id'];
        $userId = self::createUser()['id'];

        $data = [
            Comment::TEXT_ATTR => '    ',
            Comment::SERIES_ATTR => $seriesId,
            Comment::USER_ATTR => $userId
        ];

        self::$client->request(
            'POST',
            CommentApiController::COMMENT_API_ROUTE,
            [], [], [],
            json_encode($data)
        );
        $response = self::$client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($response->isSuccessful());
    }
}