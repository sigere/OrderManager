<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CertifiedOrder extends Order
{
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
     * @ORM\Column(type="integer")
     */
    private int $number;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?\DateTimeInterface $documentDate;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private float $copyPrice;

    public function __construct()
    {
        parent::__construct();
        $this->copies = 0;
        $this->copyPrice = 0.0;
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

    public function getCopies(): ?int
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

    public function getDocumentDate(): ?\DateTimeInterface
    {
        return $this->documentDate;
    }

    public function setDocumentDate(?\DateTimeInterface $documentDate): self
    {
        $this->documentDate = $documentDate;

        return $this;
    }

    public function getCopyPrice(): ?string
    {
        return $this->copyPrice;
    }

    public function setCopyPrice(string $copyPrice): self
    {
        $this->copyPrice = $copyPrice;

        return $this;
    }
}
