<?php

namespace App\Entity;

use App\Repository\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=InvoiceRepository::class)
 */
class Invoice
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="integer")
     */
    private int $ordersAmount;

    /**
     * @ORM\Column(type="float")
     */
    private float $netto;

    /**
     * @ORM\ManyToMany(targetEntity=Order::class)
     */
    private $orders;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;


    public function __construct(User $user)
    {
        $this->user = $user;
        $this->createdAt = new \DateTime();
        $this->ordersAmount = 0;
        $this->netto = 0;
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getOrdersAmount(): ?int
    {
        return $this->ordersAmount;
    }

    public function getNetto(): ?float
    {
        return $this->netto;
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

    public function getUser(): ?User
    {
        return $this->user;
    }
}
