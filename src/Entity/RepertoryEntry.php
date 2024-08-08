<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'RepertoryEntryRepository')]
class RepertoryEntry
{
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: 'Order', inversedBy: 'repertoryEntry', cascade: ['persist', 'remove'])]
    private ?Order $order;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $documentIssuer;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $comments;

    #[Assert\PositiveOrZero]
    #[ORM\Column(type: 'integer')]
    private int $copies;

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $number;

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $year;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTime $documentDate;

    #[Assert\PositiveOrZero]
    #[ORM\Column(type: 'float')]
    private float $copyPrice;

    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $documentName;

    public function __construct()
    {
        $this->copies = 0;
        $this->copyPrice = 0.0;
        $this->createdAt = new \DateTime();
        $this->order = null;
        $this->documentIssuer = null;
        $this->comments = null;
    }

    public function getFormattedNumber(): string
    {
        return $this->number.'/'.$this->year;
    }

    public function getAdditionalFee(): float
    {
        return round($this->copyPrice * $this->copies, 2);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getDocumentIssuer(): ?string
    {
        return $this->documentIssuer;
    }

    public function setDocumentIssuer(?string $documentIssuer): self
    {
        $this->documentIssuer = $documentIssuer;

        return $this;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(?string $comments): self
    {
        $this->comments = $comments;

        return $this;
    }

    public function getCopies(): int
    {
        return $this->copies;
    }

    public function setCopies(int $copies): self
    {
        $this->copies = $copies;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getDocumentDate(): ?\DateTime
    {
        return $this->documentDate;
    }

    public function setDocumentDate(?\DateTimeInterface $documentDate): self
    {
        $this->documentDate = $documentDate;

        return $this;
    }

    public function getCopyPrice(): float
    {
        return $this->copyPrice;
    }

    public function setCopyPrice(float $copyPrice): self
    {
        $this->copyPrice = $copyPrice;

        return $this;
    }

    public function getCreatedAt(): \DateTime|\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getDocumentName(): ?string
    {
        return $this->documentName;
    }

    public function setDocumentName(?string $documentName): self
    {
        $this->documentName = $documentName;

        return $this;
    }
}
