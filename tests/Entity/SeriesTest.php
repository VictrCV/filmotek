<?php

namespace App\Tests\Entity;

use App\Entity\Series;
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
     * Implement testGetSetApiId().
     *
     * @covers ::getApiId
     * @covers ::setApiId
     * @return void
     * @throws Exception
     */
    public function testGetSetApiId(): void
    {
        $apiId = self::$faker->word();
        self::$series->setApiId($apiId);
        self::assertEquals($apiId, self::$series->getApiId());
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
            'id' => self::$series->getId(),
            Series::API_ID_ATTR => self::$series->getApiId(),
            Series::TITLE_ATTR => self::$series->getTitle(),
            Series::IS_FILM_ATTR => self::$series->getIsFilm(),
            Series::SYNOPSIS_ATTR => self::$series->getSynopsis(),
            Series::IMAGE_URL_ATTR => self::$series->getImageUrl(),
        ];

        self::assertEquals($vars, self::$series->jsonSerialize());
    }
}