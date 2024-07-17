<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "CompanyRepository")]
class Company
{
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 100)]
    private string $name;

    #[ORM\Column(type: "string", length: 10)]
    private string $nip;

    #[ORM\Column(type: "string", length: 255)]
    private string $address;

    #[ORM\Column(type: "string", length: 6)]
    private string $postCode;

    #[ORM\Column(type: "string", length: 255)]
    private string $city;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $bankAccount;

    #[ORM\Column(type: "date", nullable: true)]
    private ?DateTimeInterface $issueDate;

    #[ORM\Column(type: "date", nullable: true)]
    private ?DateTimeInterface $paymentTo;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $rep;

    #[ORM\Column(type: "date", nullable: true)]
    private ?DateTimeInterface $invoiceMonth;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNip(): ?string
    {
        return $this->nip;
    }

    public function setNip(string $nip): self
    {
        $this->nip = $nip;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPostCode(): ?string
    {
        return $this->postCode;
    }

    public function setPostCode(string $postCode): self
    {
        $this->postCode = $postCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getBankAccount(): ?string
    {
        return $this->bankAccount;
    }

    public function setBankAccount(?string $bankAccount): self
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    public function getIssueDate(): ?DateTimeInterface
    {
        return $this->issueDate;
    }

    public function setIssueDate(?DateTimeInterface $issueDate): self
    {
        $this->issueDate = $issueDate;

        return $this;
    }

    public function getPaymentTo(): ?DateTimeInterface
    {
        return $this->paymentTo;
    }

    public function setPaymentTo(?DateTimeInterface $paymentTo): self
    {
        $this->paymentTo = $paymentTo;

        return $this;
    }

    public function getRep(): ?string
    {
        return $this->rep;
    }

    public function setRep(?string $rep): self
    {
        $this->rep = $rep;

        return $this;
    }

    public function getInvoiceMonth(): ?DateTimeInterface
    {
        return $this->invoiceMonth;
    }

    public function setInvoiceMonth(?DateTime $invoiceMonth): self
    {
        $this->invoiceMonth = $invoiceMonth;

        return $this;
    }
}
