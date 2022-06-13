<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
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
    public const SEASON_ATTR = 'season';
    public const EPISODE_ATTR = 'episode';
    public const TIME_ATTR = 'time';

    public const TIME_FORMAT = 'H:i:s';

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

    /**
     * @var int
     *
     * @ORM\Column(name="season", type="integer")
     */
    private $season = 1;

    /**
     * @var int
     *
     * @ORM\Column(name="episode", type="integer")
     */
    private $episode = 1;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="time", type="time")
     */
    private $time;

    public function __construct()
    {
        $this->setTime(DateTime::createFromFormat(SeriesList::TIME_FORMAT, "00:00:00"));
    }

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

    public function getSeason(): ?int
    {
        return $this->season;
    }

    public function setSeason(?int $season): self
    {
        $this->season = $season;

        return $this;
    }

    public function getEpisode(): ?int
    {
        return $this->episode;
    }

    public function setEpisode(?int $episode): self
    {
        $this->episode = $episode;

        return $this;
    }

    public function getTime(): ?DateTime
    {
        return $this->time;
    }

    public function setTime(?DateTime $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        $vars = get_object_vars($this);
        $vars[SeriesList::TIME_ATTR] = $this->getTime()->format(self::TIME_FORMAT);

        return $vars;
    }
}
