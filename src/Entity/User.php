<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 *
 * @ORM\Table(name="user", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQ_USERNAME", columns={"username"})})
 * @ORM\Entity
 *
 * @UniqueEntity(fields={"username"}, message="This username already exists.")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface, JsonSerializable
{
    public const USER_ATTR = 'user';
    public const USERNAME_ATTR = 'username';
    public const PASSWORD_ATTR = 'password';
    public const ROLE_USER = 'ROLE_USER';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=32, nullable=false)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     */
    private $password;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        $roles[] = self::ROLE_USER;
        return $roles;
    }

    public function eraseCredentials()
    {
        $this->setPassword('');
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
    }

    public function jsonSerialize(): array
    {
        $vars = get_object_vars($this);
        unset($vars[self::PASSWORD_ATTR]);
        return $vars;
    }
}
