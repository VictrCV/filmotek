<?php

namespace App\Tests\Entity;

use App\Entity\Series;
use App\Entity\SeriesList;
use App\Entity\User;
use DateTime;
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
     * @return void
     * @throws Exception
     */
    public function testConstructor(): void
    {
        $seriesList = new SeriesList();
        $defaultTime = DateTime::createFromFormat("H:i:s", "00:00:00");
        self::assertEquals($defaultTime, $seriesList->getTime());
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
     * Implement testGetSetTime().
     *
     * @covers ::getTime
     * @covers ::setTime
     * @return void
     * @throws Exception
     */
    public function testGetSetTime(): void
    {
        $datetime = self::$faker->time();
        $time = DateTime::createFromFormat("H:i:s", $datetime);
        self::$seriesList->setTime($time);
        self::assertEquals($time, self::$seriesList->getTime());
    }

    /**
     * Implement testGetSetEpisode().
     *
     * @covers ::getEpisode
     * @covers ::setEpisode
     * @return void
     * @throws Exception
     */
    public function testGetSetEpisode(): void
    {
        $episode = self::$faker->randomNumber();
        self::$seriesList->setEpisode($episode);
        self::assertEquals($episode, self::$seriesList->getEpisode());
    }

    /**
     * Implement testGetSetSeason().
     *
     * @covers ::getSeason
     * @covers ::setSeason
     * @return void
     * @throws Exception
     */
    public function testGetSetSeason(): void
    {
        $season = self::$faker->randomNumber();
        self::$seriesList->setSeason($season);
        self::assertEquals($season, self::$seriesList->getSeason());
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
            SeriesList::USER_ATTR => self::$seriesList->getUser(),
            Series::SEASON_ATTR => self::$seriesList->getSeason(),
            Series::EPISODE_ATTR => self::$seriesList->getEpisode(),
            Series::TIME_ATTR => self::$seriesList->getTime()->format('H:i:s'),
        ];
        self::assertEquals($vars, self::$seriesList->jsonSerialize());
    }
}