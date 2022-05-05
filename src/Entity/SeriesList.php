<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SeriesList
 *
 * @ORM\Table(name="series_list", indexes={@ORM\Index(name="userId", columns={"userId"}), @ORM\Index(name="seriesId", columns={"seriesId"})})
 * @ORM\Entity
 */
class SeriesList
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var array
     *
     * @ORM\Column(name="type", type="simple_array", length=0, nullable=false)
     */
    private $type;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="userId", referencedColumnName="id")
     * })
     */
    private $userid;

    /**
     * @var Series
     *
     * @ORM\ManyToOne(targetEntity="Series")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="seriesId", referencedColumnName="id")
     * })
     */
    private $seriesid;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?array
    {
        return $this->type;
    }

    public function setType(array $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getUserid(): ?User
    {
        return $this->userid;
    }

    public function setUserid(?User $userid): self
    {
        $this->userid = $userid;

        return $this;
    }

    public function getSeriesid(): ?Series
    {
        return $this->seriesid;
    }

    public function setSeriesid(?Series $seriesid): self
    {
        $this->seriesid = $seriesid;

        return $this;
    }


}
