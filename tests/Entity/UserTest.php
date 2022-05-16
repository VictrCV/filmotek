<?php

namespace App\Tests\Entity;

use App\Entity\User;
use Exception;
use Faker\Factory as FakerFactoryAlias;
use Faker\Generator as FakerGeneratorAlias;
use PHPUnit\Framework\TestCase;

/**
 * Class UserTest
 *
 * @package App\Tests\Entity
 * @group   entities
 *
 * @coversDefaultClass \App\Entity\User
 */
class UserTest extends TestCase
{
    protected static User $user;
    private static FakerGeneratorAlias $faker;

    /**
     * Sets up the fixture.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public static function setupBeforeClass(): void
    {
        self::$user = new User();
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
        self::assertEmpty(self::$user->getId());
    }

    /**
     * Implement testGetSetUsername().
     *
     * @covers ::getUsername
     * @covers ::setUsername
     * @return void
     * @throws Exception
     */
    public function testGetSetUsername(): void
    {
        $username = self::$faker->userName();
        self::$user->setUsername($username);
        self::assertEquals($username, self::$user->getUsername());
    }

    /**
     * Implement testGetSetPassword().
     *
     * @covers ::getPassword
     * @covers ::setPassword
     * @return void
     * @throws Exception
     */
    public function testGetSetPassword(): void
    {
        $password = self::$faker->password();
        self::$user->setPassword($password);
        self::assertEquals($password, self::$user->getPassword());
    }

}