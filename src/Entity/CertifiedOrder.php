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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $refusal;

    public function __construct()
    {
        parent::__construct();
        $this->copies = 0;
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

    public function getRefusal(): ?string
    {
        return $this->refusal;
    }

    public function setRefusal(?string $refusal): self
    {
        $this->refusal = $refusal;

        return $this;
    }
}