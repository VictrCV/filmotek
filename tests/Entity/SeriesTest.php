<?php

namespace App\Tests\Entity;

use App\Entity\Series;
use DateTime;
use Exception;
use Faker\Factory as FakerFactoryAlias;
use Faker\Generator as FakerGeneratorAlias;
use PHPUnit\Framework\TestCase;

/**
 * Class SeriesTest
 *
 * @package App\Tests\Entity
 * @group   entities
 *
 * @coversDefaultClass \App\Entity\Series
 */
class SeriesTest extends TestCase
{
    protected static Series $series;
    private static FakerGeneratorAlias $faker;

    /**
     * Sets up the fixture.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public static function setupBeforeClass(): void
    {
        self::$series = new Series();
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
        self::assertEmpty(self::$series->getId());
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
        self::$series->setTime($time);
        self::assertEquals($time, self::$series->getTime());
    }

    /**
     * Implement testGetSetChapter().
     *
     * @covers ::getChapter
     * @covers ::setChapter
     * @return void
     * @throws Exception
     */
    public function testGetSetChapter(): void
    {
        $chapter = self::$faker->randomNumber();
        self::$series->setChapter($chapter);
        self::assertEquals($chapter, self::$series->getChapter());
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
        self::$series->setSeason($season);
        self::assertEquals($season, self::$series->getSeason());
    }

    /**
     * Implement testGetSetIsFilm().
     *
     * @covers ::getIsFilm
     * @covers ::setIsFilm
     * @return void
     * @throws Exception
     */
    public function testGetSetIsFilm(): void
    {
        $isFilm = self::$faker->boolean();
        self::$series->setIsFilm($isFilm);
        self::assertEquals($isFilm, self::$series->getIsFilm());
    }

    /**
     * Implement testGetSetDataImdbId().
     *
     * @covers ::getDataImdbId
     * @covers ::setDataImdbId
     * @return void
     * @throws Exception
     */
    public function testGetSetDataImdbId(): void
    {
        $dataImdbId = self::$faker->word();
        self::$series->setDataImdbId($dataImdbId);
        self::assertEquals($dataImdbId, self::$series->getDataImdbId());
    }

    /**
     * Implement testGetSetImageUrl().
     *
     * @covers ::getImageUrl
     * @covers ::setImageUrl
     * @return void
     * @throws Exception
     */
    public function testGetSetImageUrl(): void
    {
        $imageUrl = self::$faker->word();
        self::$series->setImageUrl($imageUrl);
        self::assertEquals($imageUrl, self::$series->getImageUrl());
    }

    /**
     * Implement testGetSetTitle().
     *
     * @covers ::getTitle
     * @covers ::setTitle
     * @return void
     * @throws Exception
     */
    public function testGetSetTitle(): void
    {
        $title = self::$faker->sentence();
        self::$series->setTitle($title);
        self::assertEquals($title, self::$series->getTitle());
    }

    /**
     * Implement testGetSetSynopsis().
     *
     * @covers ::getSynopsis
     * @covers ::setSynopsis
     * @return void
     * @throws Exception
     */
    public function testGetSetSynopsis(): void
    {
        $synopsis = self::$faker->paragraph();
        self::$series->setSynopsis($synopsis);
        self::assertEquals($synopsis, self::$series->getSynopsis());
    }
}