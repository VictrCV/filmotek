<?php

namespace App\Tests\Entity;

use App\Entity\Rating;
use App\Entity\Series;
use App\Entity\User;
use Exception;
use Faker\Factory as FakerFactoryAlias;
use Faker\Generator as FakerGeneratorAlias;
use PHPUnit\Framework\TestCase;

/**
 * @package App\Tests\Entity
 * @group   entities
 *
 * @coversDefaultClass \App\Entity\Rating
 */
class RatingTest extends TestCase
{
    protected static Rating $rating;
    private static FakerGeneratorAlias $faker;

    /**
     * Sets up the fixture.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public static function setupBeforeClass(): void
    {
        self::$rating = new Rating();
        self::$faker = FakerFactoryAlias::create();
    }

    /**
     * @covers ::getId
     * @return void
     * @throws Exception
     */
    public function testGetId(): void
    {
        self::assertEmpty(self::$rating->getId());
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
        self::$rating->setSeries($series);
        self::assertEquals($series, self::$rating->getSeries());
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
        self::$rating->setUser($user);
        self::assertEquals($user, self::$rating->getUser());
    }

    /**
     * @covers ::getValue
     * @covers ::setValue
     * @return void
     * @throws Exception
     */
    public function testGetSetValue(): void
    {
        $value = self::$faker->numberBetween(1, 5);
        self::$rating->setValue($value);
        self::assertEquals($value, self::$rating->getValue());
    }

    /**
     * @covers ::jsonSerialize
     * @return void
     * @throws Exception
     */
    public function testJsonSerialize(): void
    {
        $vars = [
            'id' => self::$rating->getId(),
            Rating::VALUE_ATTR => self::$rating->getValue(),
            Rating::SERIES_ATTR => self::$rating->getSeries(),
            Rating::USER_ATTR => self::$rating->getUser(),
        ];

        self::assertEquals($vars, self::$rating->jsonSerialize());
    }
}