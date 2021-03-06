<?php

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\Series;
use App\Entity\User;
use DateTime;
use Exception;
use Faker\Factory as FakerFactoryAlias;
use Faker\Generator as FakerGeneratorAlias;
use PHPUnit\Framework\TestCase;

/**
 * @package App\Tests\Entity
 * @group   entities
 *
 * @coversDefaultClass \App\Entity\Comment
 */
class CommentTest extends TestCase
{
    protected static Comment $comment;
    private static FakerGeneratorAlias $faker;

    /**
     * Sets up the fixture.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public static function setupBeforeClass(): void
    {
        self::$comment = new Comment();
        self::$faker = FakerFactoryAlias::create();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testConstructor(): void
    {
        $comment = new Comment();
        self::assertNotEmpty($comment->getDatetime());
    }

    /**
     * @covers ::getId
     * @return void
     * @throws Exception
     */
    public function testGetId(): void
    {
        self::assertEmpty(self::$comment->getId());
    }

    /**
     * @covers ::getSeries
     * @covers ::setSeries
     * @return void
     * @throws Exception
     */
    public function testGetSetSeries(): void
    {
        $series = new Series();
        self::$comment->setSeries($series);
        self::assertEquals($series, self::$comment->getSeries());
    }

    /**
     * @covers ::getUser
     * @covers ::setUser
     * @return void
     * @throws Exception
     */
    public function testGetSetUser(): void
    {
        $user = new User();
        self::$comment->setUser($user);
        self::assertEquals($user, self::$comment->getUser());
    }

    /**
     * @covers ::getText
     * @covers ::setText
     * @return void
     * @throws Exception
     */
    public function testGetSetText(): void
    {
        $text = self::$faker->text();
        self::$comment->setText($text);
        self::assertEquals($text, self::$comment->getText());
    }

    /**
     * @covers ::getDatetime
     * @covers ::setDatetime
     * @return void
     * @throws Exception
     */
    public function testGetSetDatetime(): void
    {
        $datetime = self::$faker->dateTime();
        self::$comment->setDatetime($datetime);
        self::assertEquals($datetime, self::$comment->getDatetime());
    }

    /**
     * @covers ::jsonSerialize
     * @return void
     * @throws Exception
     */
    public function testJsonSerialize(): void
    {
        $vars = [
            'id' => self::$comment->getId(),
            Comment::TEXT_ATTR => self::$comment->getText(),
            Comment::DATETIME_ATTR => self::$comment->getDatetime()->format('d-m-Y H:i'),
            Comment::SERIES_ATTR => self::$comment->getSeries(),
            Comment::USER_ATTR => self::$comment->getUser(),
        ];
        self::assertEquals($vars, self::$comment->jsonSerialize());
    }
}