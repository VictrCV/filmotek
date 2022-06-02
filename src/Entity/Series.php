<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Series
 *
 * @ORM\Table(name="series", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQ_API_ID", columns={"apiId"})})
 * @ORM\Entity
 *
 * @UniqueEntity(fields={"apiId"}, message="This series already exists.")
 */
class Series implements JsonSerializable
{
    public const SERIES_ATTR = 'series';
    public const API_ID_ATTR = 'apiId';
    public const TITLE_ATTR = 'title';
    public const IS_FILM_ATTR = 'isFilm';
    public const SYNOPSIS_ATTR = 'synopsis';
    public const IMAGE_URL_ATTR = 'imageUrl';

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

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }
}
