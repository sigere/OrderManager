<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

// TODO translations
#[UniqueEntity(fields: ['alias'], message: 'constraints.client.alias.exists', repositoryMethod: 'findByAliasIgnoreCase')]
#[UniqueEntity(fields: ['nip'], message: 'Istnieje już klient o podanym NIPie!')]
#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 50)]
    private string $alias;

    #[ORM\Column(type: 'string', length: 15)]
    #[Assert\Regex("/\d{10}/")]
    #[Assert\Length(min: 10, max: 10)]
    private string $nip;

    #[ORM\Column(type: 'string', length: 6)]
    private string $postCode;

    #[ORM\Column(type: 'string', length: 50)]
    private string $city;

    #[ORM\Column(type: 'string', length: 255)]
    private string $street;

    #[Assert\Country(message: 'Wprowadź poprawny kraj.')]
    #[ORM\Column(type: 'string', length: 2)]
    private string $country;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $deletedAt;

    #[Assert\Email(message: "Adres email '{{ value }}' nie jest poprawny.")]
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $email;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->deletedAt = null;
    }

    public function __toString(): string
    {
        return $this->getAlias();
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

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

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTime $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }
}
