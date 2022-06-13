<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * Comment
 *
 * @ORM\Table(name="comment", indexes={@ORM\Index(name="FK_9474526C64B64DCC", columns={"userId"}), @ORM\Index(name="FK_9474526CF891D8C1", columns={"seriesId"})})
 * @ORM\Entity
 */
class Comment implements JsonSerializable
{
    public const COMMENT_ATTR = 'comment';
    public const TEXT_ATTR = 'text';
    public const DATETIME_ATTR = 'datetime';
    public const SERIES_ATTR = 'series';
    public const USER_ATTR = 'user';

    public const DATETIME_FORMAT = 'd-m-Y H:i';

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
     * @ORM\Column(name="text", type="text", length=0, nullable=false)
     */
    private $text;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="datetime", type="datetime", nullable=false)
     */
    private $datetime;

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

    public function __construct()
    {
        $this->setDatetime(new DateTime());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getDatetime(): DateTime
    {
        return $this->datetime;
    }

    public function setDatetime(DateTime $datetime): void
    {
        $this->datetime = $datetime;
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
        $vars = get_object_vars($this);
        $vars[Comment::DATETIME_ATTR] = $this->getDatetime()->format(self::DATETIME_FORMAT);

        return $vars;
    }
}
