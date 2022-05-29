<?php

namespace App\Tests\Entity;

use App\Entity\Series;
use App\Entity\SeriesList;
use App\Entity\User;
use Exception;
use Faker\Factory as FakerFactoryAlias;
use Faker\Generator as FakerGeneratorAlias;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Class SeriesListTest
 *
 * @package App\Tests\Entity
 * @group   entities
 *
 * @coversDefaultClass \App\Entity\SeriesList
 */
class SeriesListTest extends TestCase
{
    protected static SeriesList $seriesList;
    private static FakerGeneratorAlias $faker;

    /**
     * Sets up the fixture.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public static function setupBeforeClass(): void
    {
        self::$seriesList = new SeriesList();
        self::$faker = FakerFactoryAlias::create();
    }

    /**
     * Implement testGetId().
     *
     * @covers ::getId
     * @return void
     * @throws Exception
     */
    public function testGetId(): void
    {
        self::assertEmpty(self::$seriesList->getId());
    }

    /**
     * Implement testGetSetSeries().
     *
     * @covers ::getSeries
     * @covers ::setSeries
     * @return void
     * @throws Exception
     */
    public function testGetSetSeries(): void
    {
        $series = new Series();
        self::$seriesList->setSeries($series);
        self::assertEquals($series, self::$seriesList->getSeries());
    }

    /**
     * Implement testGetSetUser().
     *
     * @covers ::getUser
     * @covers ::setUser
     * @return void
     * @throws Exception
     */
    public function testGetSetUser(): void
    {
        $user = new User();
        self::$seriesList->setUser($user);
        self::assertEquals($user, self::$seriesList->getUser());
    }

    /**
     * Implement testGetSetType().
     *
     * @covers ::getType
     * @covers ::setType
     * @return void
     * @throws Exception
     */
    public function testGetSetType(): void
    {
        $type = SeriesList::FAVOURITES;
        self::$seriesList->setType($type);
        self::assertEquals($type, self::$seriesList->getType());
        $invalidType = self::$faker->sentence();
        self::expectException(InvalidArgumentException::class);
        self::$seriesList->setType($invalidType);
    }

    /**
     * Implement testJsonSerialize().
     *
     * @covers ::jsonSerialize
     * @return void
     * @throws Exception
     */
    public function testJsonSerialize(): void
    {
        $vars = [
            'id' => self::$seriesList->getId(),
            SeriesList::TYPE_ATTR => self::$seriesList->getType(),
            SeriesList::SERIES_ATTR => self::$seriesList->getSeries(),
            SeriesList::USER_ATTR => self::$seriesList->getUser()
        ];
        self::assertEquals($vars, self::$seriesList->jsonSerialize());
    }
}