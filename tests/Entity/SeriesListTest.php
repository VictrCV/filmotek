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
     * @return void
     * @throws Exception
     */
    public function testConstructor(): void
    {
        $seriesList = new SeriesList();
        $defaultTime = DateTime::createFromFormat(SeriesList::TIME_FORMAT, "00:00:00");
        self::assertEquals($defaultTime, $seriesList->getTime());
    }

    /**
     * @covers ::getId
     * @return void
     * @throws Exception
     */
    public function testGetId(): void
    {
        self::assertEmpty(self::$seriesList->getId());
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
        self::$seriesList->setSeries($series);
        self::assertEquals($series, self::$seriesList->getSeries());
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
        self::$seriesList->setUser($user);
        self::assertEquals($user, self::$seriesList->getUser());
    }

    /**
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
     * @covers ::getTime
     * @covers ::setTime
     * @return void
     * @throws Exception
     */
    public function testGetSetTime(): void
    {
        $datetime = self::$faker->time();
        $time = DateTime::createFromFormat(SeriesList::TIME_FORMAT, $datetime);
        self::$seriesList->setTime($time);
        self::assertEquals($time, self::$seriesList->getTime());
    }

    /**
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
            SeriesList::SEASON_ATTR => self::$seriesList->getSeason(),
            SeriesList::EPISODE_ATTR => self::$seriesList->getEpisode(),
            SeriesList::TIME_ATTR => self::$seriesList->getTime()->format(SeriesList::TIME_FORMAT),
        ];
        self::assertEquals($vars, self::$seriesList->jsonSerialize());
    }
}