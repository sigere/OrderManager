<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order
{

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
     * @ORM\Column(type="boolean")
     */
    private $deleted;

    /**
     * @ORM\Column(type="boolean")
     */
    private $certified;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $pages;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255)
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

    public function __construct(){
        $this->deleted = false;
        $this->state = 'przyjęte';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

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

    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    public function setStaff(?Staff $staff): self
    {
        $this->staff = $staff;

        return $this;
    }

    public function getBaseLang(): ?Lang
    {
        return $this->baseLang;
    }

    public function setBaseLang(?Lang $baseLang): self
    {
        $this->baseLang = $baseLang;

        return $this;
    }

    public function getTargetLang(): ?Lang
    {
        return $this->targetLang;
    }

    public function setTargetLang(?Lang $targetLang): self
    {
        $this->targetLang = $targetLang;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

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
        return $this->adoption->format('Y-m-d H:i');
    }

    public function setAdoption(\DateTimeInterface $adoption): self
    {
        $this->adoption = $adoption;

        return $this;
    }

    public function getDeadline()
    {
        return $this->deadline->format('Y-m-d H:i');
    }

    public function setDeadline(\DateTimeInterface $deadline): self
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function nextState(): ?string{
        switch($this->state){
            case 'przyjęte':
                return 'wykonane';
            case 'wykonane':
                return 'wysłane';
            case 'wysłane':
                return 'rozliczone';
            default:
                return null;
        }
    }

    public function getNetto(){
        if($this->price && $this->pages)
            return round($this->price * $this->pages, 2);
        return null;
    }

    public function __toString(): String{
        return $this->getId();
    }

    public function getAllColumns(){
        return [
            "client",
            "author",
            "staff",
            "baseLang",
            "targetLang",
            "deleted",
            "certified",
            "pages",
            "price",
            "netto",
            "topic",
            "state",
            "info",
            "adoption",
            "deadline",
        ];
    }
}
