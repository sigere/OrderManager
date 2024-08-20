<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\OrderState;
use App\Repository\OrderRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: '`order`')]
#[ORM\Entity(repositoryClass: OrderRepository::class)]
class Order
{
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\JoinColumn(nullable: false)]
    #[ManyToOne(targetEntity: 'Client')]
    private Client $client;

    #[ORM\JoinColumn(nullable: false)]
    #[ManyToOne(targetEntity: 'User')]
    private User $author;

    #[ORM\JoinColumn(nullable: false)]
    #[ManyToOne(targetEntity: 'Staff')]
    private Staff $staff;

    #[ORM\JoinColumn(nullable: false)]
    #[ManyToOne(targetEntity: 'Lang')]
    private Lang $baseLang;

    #[ORM\JoinColumn(nullable: false)]
    #[ManyToOne(targetEntity: 'Lang')]
    private Lang $targetLang;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $deletedAt = null;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $certified;

    #[Assert\PositiveOrZero]
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $pages;

    #[Assert\PositiveOrZero]
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $price;

    #[Assert\PositiveOrZero]
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $additionalFee;

    #[Assert\NotBlank(message: 'order.topic.not_blank')]
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $topic;

    #[ORM\Column(type: 'string', enumType: OrderState::class)]
    private OrderState $state = OrderState::Accepted;

    #[ORM\Column(type: 'text', nullable: false)]
    private string $info;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $adoption;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $deadline;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $settledAt = null;

    #[ORM\OneToOne(targetEntity: 'RepertoryEntry', mappedBy: 'order', cascade: ['persist', 'remove'])]
    private ?RepertoryEntry $repertoryEntry;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoiceWarnings(): array
    {
        $warnings = $this->getWarnings();
        if (OrderState::Sent != $this->state) {
            $warnings[] = 'Zlecenie nie zostało wysłane.';
        }

        return $warnings;
    }

    // todo refactor
    public function getWarnings(): array
    {
        $warnings = [];
        //        $now = new DateTime();
        //        $timeToDeadline = $this->deadline->getTimestamp() - $now->getTimestamp();
        //
        //        if (0 == $this->price) {
        //            $warnings[] = 'Cena za stronę jest równa 0.';
        //        }
        //
        //        switch ($this->state) {
        //            case self::ACCEPTED:
        //                if ($timeToDeadline < 0) {
        //                    $warnings[] = 'Minął termin zlecenia, a jego status jest ustawiony na przyjęte';
        //                } elseif ($timeToDeadline < 86400) {
        //                    $warnings[] = 'Pozostało mniej niż 24h do terminu zlecenia, a jego status jest ustawiony na przyjęte';
        //                }
        //                break;
        //            case self::DONE:
        //                if ($timeToDeadline < 0) {
        //                    $warnings[] = 'Minął termin zlecenia, a jego status jest ustawiony na wykonane';
        //                }
        //                if (0 == $this->pages) {
        //                    $warnings[] = 'Status zlecenia został ustawiony na wykonane, a liczba stron jest równa 0.';
        //                }
        //                break;
        //            case self::SENT:
        //                if (0 == $this->pages) {
        //                    $warnings[] = 'Status zlecenia został ustawiony na wysłane, a liczba stron jest równa 0.';
        //                }
        //                break;
        //        }

        return $warnings;
    }

    public function getNetto(): float
    {
        if (!$this->price || !$this->pages) {
            return 0.0;
        }

        $result = round($this->price * $this->pages, 2);
        $result += $this->repertoryEntry?->getAdditionalFee() ?? 0.0;
        $result += $this->additionalFee ?? 0.0;

        return $result;
    }

    public function getAdditionalFee(): ?float
    {
        return $this->additionalFee;
    }

    public function setAdditionalFee(?float $additionalFee): self
    {
        $this->additionalFee = $additionalFee;

        return $this;
    }

    public function getBrutto(): float
    {
        if ($this->price && $this->pages) {
            return round($this->price * $this->pages * 1.23, 2);
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

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTime $dateTime): self
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

    public function getPages(): ?float
    {
        return $this->pages;
    }

    public function setPages(?float $pages): self
    {
        $this->pages = $pages;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
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

    public function getState(): OrderState
    {
        return $this->state;
    }

    public function setState(OrderState $state): self
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

    public function getAdoption(): \DateTime
    {
        return $this->adoption;
    }

    public function setAdoption(\DateTime $adoption): self
    {
        $this->adoption = $adoption;

        return $this;
    }

    public function getDeadline(): \DateTime
    {
        return $this->deadline;
    }

    public function setDeadline(\DateTime $deadline): self
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getSettledAt(): ?\DateTimeInterface
    {
        return $this->settledAt;
    }

    public function setSettledAt(?\DateTimeInterface $settledAt): self
    {
        $this->settledAt = $settledAt;

        return $this;
    }

    public function getRepertoryEntry(): ?RepertoryEntry
    {
        return $this->repertoryEntry;
    }

    public function setRepertoryEntry(?RepertoryEntry $repertoryEntry): self
    {
        if (null === $repertoryEntry && null !== $this->repertoryEntry) {
            $this->repertoryEntry->setOrder(null);
        }

        if (null !== $repertoryEntry && $repertoryEntry->getOrder() !== $this) {
            $repertoryEntry->setOrder($this);
        }

        $this->repertoryEntry = $repertoryEntry;

        return $this;
    }
}
