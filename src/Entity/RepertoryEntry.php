<?php

namespace App\Entity;

use App\Repository\RepertoryEntryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RepertoryEntryRepository::class)
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
    private Order $order;

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
     */
    private int $copies;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $number;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $year;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?\DateTimeInterface $documentDate;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private float $copyPrice;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     */
    private \DateTimeInterface $createdAt;

    public function __construct(RepertoryEntryRepository $repository, Order $order)
    {
        $this->order = $order;
        $this->copies = 0;
        $this->copyPrice = 0.0;
        $this->createdAt = new \DateTimeImmutable();

        $this->year = (int) $order->getDeadline()->format('Y');
        $this->number = $repository->getNumber($this->year);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @param Order $order
     * @return RepertoryEntry
     */
    public function setOrder(Order $order): RepertoryEntry
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
    public function setDocumentIssuer(?string $documentIssuer): RepertoryEntry
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
    public function setComments(?string $comments): RepertoryEntry
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
    public function setCopies(int $copies): RepertoryEntry
    {
        $this->copies = $copies;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return int
     */
    public function getYear(): int
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
    public function setDocumentDate(?\DateTimeInterface $documentDate): RepertoryEntry
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
    public function setCopyPrice(float $copyPrice): RepertoryEntry
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
}
