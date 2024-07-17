<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "InvoiceRepository")]
class Invoice
{
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\Column(type: "datetime")]
    private DateTime $createdAt;

    #[ORM\Column(type: "integer")]
    private int $ordersAmount;

    #[ORM\Column(type: "float")]
    private float $netto;

    #[ORM\ManyToMany(targetEntity: "Order")]
    #[ORM\Column(type: 'string')]
    private ArrayCollection $orders;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: "User")]
    private User $user;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->ordersAmount = 0;
        $this->netto = 0;
        $this->orders = new ArrayCollection();
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrdersAmount(): ?int
    {
        return $this->ordersAmount;
    }

    public function getOrders(): ArrayCollection|array|Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
        }

        $this->ordersAmount = count($this->orders);
        $this->netto += $order->getNetto();

        return $this;
    }

    public function getNetto(): ?float
    {
        return $this->netto;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): Invoice
    {
        $this->user = $user;

        return $this;
    }
}
