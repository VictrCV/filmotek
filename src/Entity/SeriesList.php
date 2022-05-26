<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use JetBrains\PhpStorm\Internal\TentativeType;
use JsonSerializable;

/**
 * SeriesList
 *
 * @ORM\Table(name="series_list", indexes={@ORM\Index(name="userId", columns={"userId"}), @ORM\Index(name="seriesId", columns={"seriesId"})})
 * @ORM\Entity
 */
class SeriesList implements JsonSerializable
{
    public const FAVOURITES = "favourites";
    public const TO_WATCH = "toWatch";
    public const IN_PROGRESS = "inProgress";

    public const SERIES_LIST_ATTR = 'series_list';
    public const TYPE_ATTR = 'type';
    public const SERIES_ATTR = 'series';
    public const USER_ATTR = 'user';

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
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    private $type;

    /**
     * @var Series
     *
     * @ORM\ManyToOne(targetEntity="Series")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="seriesId", referencedColumnName="id")
     * })
     */
    private $series;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="userId", referencedColumnName="id")
     * })
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if (!in_array($type, array(self::FAVOURITES, self::TO_WATCH, self::IN_PROGRESS))) {
            throw new InvalidArgumentException("Invalid series list type");
        }

        $this->type = $type;

        return $this;
    }

    public function getSeries(): ?Series
    {
        return $this->series;
    }

    public function setSeries(?Series $series): self
    {
        $this->series = $series;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }
}
