<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "TaskRepository")]
class Task
{
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $topic;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $info;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?DateTime $deadline;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?DateTime $deletedAt = null;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: "User")]
    private User $author;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: "User")]
    private User $target;

    #[ORM\Column(type: "datetime")]
    private DateTime $createdAt;


    #[ORM\Column(type: "datetime", nullable: true)]
    private ?DateTime $doneAt;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setInfo(?string $info): self
    {
        $this->info = $info;

        return $this;
    }

    public function getDeadline(): ?DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(?DateTimeInterface $deadline): self
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getTarget(): ?User
    {
        return $this->target;
    }

    public function setTarget(?User $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDoneAt(): ?DateTime
    {
        return $this->doneAt;
    }

    public function setDoneAt(?DateTime $doneAt): self
    {
        $this->doneAt = $doneAt;

        return $this;
    }

    // todo warnings
    public function getWarnings(): array
    {
        if ($this->doneAt) {
            return [];
        }
        $warnings = [];
        $now = new DateTime();
        $timeToDeadline = $this->deadline->getTimestamp() - $now->getTimestamp();
        if ($timeToDeadline < 0) {
            $warnings[] = 'Minął termin zadania.';
        } elseif ($timeToDeadline < 86400) {
            $warnings[] = 'Pozostało mniej niż 24h do terminu zadania.';
        }

        return $warnings;
    }
}
