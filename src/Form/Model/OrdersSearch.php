<?php
declare(strict_types=1);

namespace App\Form\Model;

use App\Form\Constraint\OneOfNotEmpty;

#[OneOfNotEmpty(['id', 'phrase'])]
class OrdersSearch
{
    private ?int $id = null;
    private ?string $phrase = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getPhrase(): ?string
    {
        return $this->phrase;
    }

    public function setPhrase(?string $phrase): void
    {
        $this->phrase = $phrase;
    }
}