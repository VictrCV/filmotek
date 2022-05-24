<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * Series
 *
 * @ORM\Table(name="series")
 * @ORM\Entity
 */
class Series implements JsonSerializable
{
    public const SERIES_ATTR = 'series';
    public const API_ID_ATTR = 'apiId';
    public const TITLE_ATTR = 'title';
    public const IS_FILM_ATTR = 'isFilm';
    public const SYNOPSIS_ATTR = 'synopsis';
    public const IMAGE_URL_ATTR = 'imageUrl';
    public const SEASON_ATTR = 'season';
    public const CHAPTER_ATTR = 'chapter';
    public const TIME_ATTR = 'time';

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
     * @ORM\Column(name="apiId", type="string", length=9, nullable=false)
     */
    private $apiId;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var bool
     *
     * @ORM\Column(name="isFilm", type="boolean", nullable=false)
     */
    private $isFilm;

    /**
     * @var string
     *
     * @ORM\Column(name="synopsis", type="text", length=0, nullable=false)
     */
    private $synopsis;

    /**
     * @var string
     *
     * @ORM\Column(name="imageUrl", type="string", length=255, nullable=false)
     */
    private $imageUrl;

    /**
     * @var int|null
     *
     * @ORM\Column(name="season", type="integer", nullable=true)
     */
    private $season;

    /**
     * @var int|null
     *
     * @ORM\Column(name="chapter", type="integer", nullable=true)
     */
    private $chapter;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="time", type="time", nullable=true)
     */
    private $time;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApiId(): ?string
    {
        return $this->apiId;
    }

    public function setApiId(string $apiId): self
    {
        $this->apiId = $apiId;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getIsFilm(): ?bool
    {
        return $this->isFilm;
    }

    public function setIsFilm(bool $isFilm): self
    {
        $this->isFilm = $isFilm;

        return $this;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(string $synopsis): self
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

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

    public function getChapter(): ?int
    {
        return $this->chapter;
    }

    public function setChapter(?int $chapter): self
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function getTime(): ?DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(?DateTimeInterface $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
