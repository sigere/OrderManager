<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order
{
    public const ALLCOLUMNS = [
        'client',
        'author',
        'staff',
        'baseLang',
        'targetLang',
        'deleted',
        'certified',
        'pages',
        'price',
        'netto',
        'topic',
        'state',
        'info',
        'adoption',
        'deadline',
    ];
    public const PRZYJETE = 'przyjete';
    public const WYKONANE = 'wykonane';
    public const WYSLANE = 'wyslane';
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\ManyToOne(targetEntity=Client::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;
    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;
    /**
     * @ORM\ManyToOne(targetEntity=Staff::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $staff;
    /**
     * @ORM\ManyToOne(targetEntity=Lang::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $baseLang;
    /**
     * @ORM\ManyToOne(targetEntity=Lang::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $targetLang;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;
    /**
     * @ORM\Column(type="boolean")
     */
    private $certified;
    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     * @Assert\PositiveOrZero
     */
    private $pages;
    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     * @Assert\PositiveOrZero
     */
    private $price;
    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="Temat nie może być pusty")
     */
    private $topic;
    /**
     * @ORM\Column(type="string", length=20)
     */
    private $state;
    /**
     * @ORM\Column(type="text")
     */
    private $info;
    /**
     * @ORM\Column(type="datetime")
     */
    private $adoption;
    /**
     * @ORM\Column(type="datetime")
     */
    private $deadline;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $settledAt;

    public function __construct()
    {
        $this->deletedAt = null;
        $this->state = self::PRZYJETE;
        $this->settledAt = null;
    }

    public function __toString(): string
    {
        return $this->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoiceWarnings(): array
    {
        $warnings = $this->getWarnings();
        if ($this->state != self::WYSLANE)
            $warnings[] = "Zlecenie nie zostało wysłane.";
        return $warnings;
    }

    public function getWarnings(): array
    {
        $warnings = [];
        $now = new DateTime;
        $timeToDeadline = $this->deadline->getTimestamp() - $now->getTimestamp();

        if ($this->price == 0)
            $warnings[] = "Cena za stronę jest równa 0.";

        switch ($this->state) {
            case self::PRZYJETE:
                if ($timeToDeadline < 0)
                    $warnings[] = "Minął termin zlecenia, a jego status jest ustawiony na przyjęte";
                else if ($timeToDeadline < 86400)
                    $warnings[] = "Pozostało mniej niż 24h do terminu zlecenia, a jego status jest ustawiony na przyjęte";
                break;
            case self::WYKONANE:
                if ($timeToDeadline < 0)
                    $warnings[] = "Minął termin zlecenia, a jego status jest ustawiony na wykonane";
                if ($this->pages == 0)
                    $warnings[] = "Status zlecenia został ustawiony na wykonane, a liczba stron jest równa 0.";
                break;
            case self::WYSLANE:
                if ($this->pages == 0) $warnings[] = "Status zlecenia został ustawiony na wysłane, a liczba stron jest równa 0.";
                break;
        }
        return $warnings;
    }

    public function nextState(): string
    {
        switch ($this->state) {
            case self::PRZYJETE:
                return self::WYKONANE;
            case self::WYKONANE:
                return self::WYSLANE;
            default:
                return '';
        }
    }

    public function getNetto(): float
    {
        if ($this->price && $this->pages) {
            return round($this->price * $this->pages, 2);
        }
        return 0.00;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    public function setStaff(Staff $staff): self
    {
        $this->staff = $staff;

        return $this;
    }

    public function getBaseLang(): ?Lang
    {
        return $this->baseLang;
    }

    public function setBaseLang(Lang $baseLang): self
    {
        $this->baseLang = $baseLang;

        return $this;
    }

    public function getTargetLang(): ?Lang
    {
        return $this->targetLang;
    }

    public function setTargetLang(Lang $targetLang): self
    {
        $this->targetLang = $targetLang;

        return $this;
    }

    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTime $dateTime): self
    {
        $this->deletedAt = $dateTime;

        return $this;
    }

    public function getCertified(): ?bool
    {
        return $this->certified;
    }

    public function setCertified(bool $certified): self
    {
        $this->certified = $certified;

        return $this;
    }

    public function getPages(): ?string
    {
        return $this->pages;
    }

    public function setPages(?string $pages): self
    {
        $this->pages = $pages;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getTopic(): ?string
    {
        return $this->topic;
    }

    public function setTopic(string $topic): self
    {
        $this->topic = $topic;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setInfo(string $info): self
    {
        $this->info = $info;

        return $this;
    }

    public function getAdoption()
    {
        return $this->adoption;
    }

    public function setAdoption(DateTimeInterface $adoption): self
    {
        $this->adoption = $adoption;

        return $this;
    }

    public function getDeadline()
    {
        return $this->deadline;
    }

    public function setDeadline(DateTimeInterface $deadline): self
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getSettledAt(): ?DateTimeInterface
    {
        return $this->settledAt;
    }

    public function setSettledAt(?DateTimeInterface $settledAt): self
    {
        $this->settledAt = $settledAt;

        return $this;
    }
}
