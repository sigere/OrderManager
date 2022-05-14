<?php

namespace App\Entity;

use App\Repository\RepertoryEntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RepertoryEntryRepository", repositoryClass=RepertoryEntryRepository::class)
 */
class RepertoryEntry
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\OneToOne(targetEntity=Order::class, inversedBy="repertoryEntry", cascade={"persist", "remove"})
     */
    private ?Order $order;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $documentIssuer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $comments;

    /**
     * @ORM\Column(type="integer")
     * @Assert\PositiveOrZero
     */
    private int $copies;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private ?int $number;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private ?int $year;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?\DateTimeInterface $documentDate;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @Assert\PositiveOrZero
     */
    private float $copyPrice;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $documentName;

    public function __construct()
    {
        $this->copies = 0;
        $this->copyPrice = 0.0;
        $this->createdAt = new \DateTimeImmutable();
        $this->order = null;
        $this->documentIssuer = null;
        $this->comments = null;
    }

    /**
     * @return array
     */
    public function getWarnings(): array
    {
        // todo
        return [];
    }

    public function getFormattedNumber(): string
    {
        return $this->number . "/" . $this->year;
    }

    public function getAdditionalFee(): float
    {
        return round($this->copyPrice * $this->copies, 2);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Order|null
     */
    public function getOrder(): ?Order
    {
        return $this->order;
    }

    /**
     * @param Order $order
     * @return RepertoryEntry
     */
    public function setOrder(Order $order): self
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDocumentIssuer(): ?string
    {
        return $this->documentIssuer;
    }

    /**
     * @param string|null $documentIssuer
     * @return RepertoryEntry
     */
    public function setDocumentIssuer(?string $documentIssuer): self
    {
        $this->documentIssuer = $documentIssuer;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComments(): ?string
    {
        return $this->comments;
    }

    /**
     * @param string|null $comments
     * @return RepertoryEntry
     */
    public function setComments(?string $comments): self
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * @return int
     */
    public function getCopies(): int
    {
        return $this->copies;
    }

    /**
     * @param int $copies
     * @return RepertoryEntry
     */
    public function setCopies(int $copies): self
    {
        $this->copies = $copies;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNumber(): ?int
    {
        return $this->number;
    }

    /**
     * @return int|null
     */
    public function getYear(): ?int
    {
        return $this->year;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDocumentDate(): ?\DateTimeInterface
    {
        return $this->documentDate;
    }

    /**
     * @param \DateTimeInterface|null $documentDate
     * @return RepertoryEntry
     */
    public function setDocumentDate(?\DateTimeInterface $documentDate): self
    {
        $this->documentDate = $documentDate;
        return $this;
    }

    /**
     * @return float
     */
    public function getCopyPrice(): float
    {
        return $this->copyPrice;
    }

    /**
     * @param float $copyPrice
     * @return RepertoryEntry
     */
    public function setCopyPrice(float $copyPrice): self
    {
        $this->copyPrice = $copyPrice;
        return $this;
    }

    /**
     * @return \DateTime|\DateTimeInterface
     */
    public function getCreatedAt(): \DateTime|\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param int $number
     * @return RepertoryEntry
     */
    public function setNumber(int $number): self
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @param int $year
     * @return RepertoryEntry
     */
    public function setYear(int $year): self
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDocumentName(): ?string
    {
        return $this->documentName;
    }

    /**
     * @param string|null $documentName
     * @return $this
     */
    public function setDocumentName(?string $documentName): self
    {
        $this->documentName = $documentName;
        return $this;
    }
}
