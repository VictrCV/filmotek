<?php

namespace App\Tests\Entity;

use App\Entity\User;
use Exception;
use Faker\Factory as FakerFactoryAlias;
use Faker\Generator as FakerGeneratorAlias;
use PHPUnit\Framework\TestCase;

/**
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
     * @covers ::getId
     * @return void
     * @throws Exception
     */
    public function testGetId(): void
    {
        self::assertEmpty(self::$user->getId());
    }

    /**
     * @covers ::getUsername
     * @covers ::setUsername
     * @return User
     * @throws Exception
     */
    public function testGetSetUsername(): User
    {
        $username = self::$faker->userName();
        self::$user->setUsername($username);
        self::assertEquals($username, self::$user->getUsername());

        return self::$user;
    }

    /**
     * @covers ::getPassword
     * @covers ::setPassword
     * @return User
     * @throws Exception
     */
    public function testGetSetPassword(): User
    {
        $password = self::$faker->password();
        self::$user->setPassword($password);
        self::assertEquals($password, self::$user->getPassword());

        return self::$user;
    }

    /**
     * @covers ::getRoles
     * @return void
     * @throws Exception
     */
    public function testGetRoles(): void
    {
        self::assertEquals(1, sizeof(self::$user->getRoles()));
        self::assertContains('ROLE_USER', self::$user->getRoles());
    }

    /**
     * @depends testGetSetPassword
     * @covers ::eraseCredentials
     * @param User $user
     * @return void
     */
    public function testEraseCredentials(User $user): void
    {
        $user->eraseCredentials();
        self::assertEmpty($user->getPassword());
    }

    /**
     * @depends testGetSetUsername
     * @covers ::getUserIdentifier
     * @param User $user
     * @return void
     */
    public function testGetUserIdentifier(User $user): void
    {
        self::assertEquals($user->getUsername(), $user->getUserIdentifier());
    }

    /**
     * @covers ::jsonSerialize
     * @return void
     * @throws Exception
     */
    public function testJsonSerialize(): void
    {
        $vars = [
            'id' => self::$user->getId(),
            User::USERNAME_ATTR => self::$user->getUsername()
        ];
        self::assertEquals($vars, self::$user->jsonSerialize());
    }

}