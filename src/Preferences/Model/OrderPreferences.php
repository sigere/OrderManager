<?php

declare(strict_types=1);

namespace App\Preferences\Model;

use App\Entity\Client;
use App\Entity\Staff;
use Symfony\Component\Validator\Constraints as Assert;

class OrderPreferences implements \JsonSerializable
{
    public const string NAME = 'orders';
    public const array COLUMNS = [
        'adoption',
        'client',
        'topic',
        'lang',
        'deadline',
        'staff',
    ];

    private ?DateType $dateType;

    #[Assert\All(
        new Assert\Choice(
            choices: OrderPreferences::COLUMNS,
            message: "'{{ value }}' is not a valid column. Use one of {{ choices }}")
    )]
    private ?array $columns;

    private ?Client $client;

    private ?Staff $staff;

    #[Assert\When(expression: 'this.getDateTo() !== null', constraints: [
        new Assert\LessThan(
            propertyPath: 'dateTo',
            message: 'The date from must be before the date to'
        ),
    ],
    )]
    private ?\DateTime $dateFrom;

    #[Assert\When(
        expression: 'this.getDateFrom() !== null',
        constraints: [
            new Assert\GreaterThan(
                propertyPath: 'dateFrom',
                message: 'The date to must be after the date from'
            ),
        ],
    )]
    private ?\DateTime $dateTo;

    private ?array $states;

    private ?bool $deleted;

    private ?bool $settled;

    public function getDateType(): ?DateType
    {
        return $this->dateType;
    }

    public function setDateType(?DateType $dateType): OrderPreferences
    {
        $this->dateType = $dateType;

        return $this;
    }

    public function getColumns(): ?array
    {
        return $this->columns;
    }

    public function setColumns(?array $columns): OrderPreferences
    {
        $this->columns = $columns;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): OrderPreferences
    {
        $this->client = $client;

        return $this;
    }

    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    public function setStaff(?Staff $staff): OrderPreferences
    {
        $this->staff = $staff;

        return $this;
    }

    public function getDateFrom(): ?\DateTime
    {
        return $this->dateFrom;
    }

    public function setDateFrom(?\DateTime $dateFrom): OrderPreferences
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    public function getDateTo(): ?\DateTime
    {
        return $this->dateTo;
    }

    public function setDateTo(?\DateTime $dateTo): OrderPreferences
    {
        $this->dateTo = $dateTo;

        return $this;
    }

    public function getStates(): ?array
    {
        return $this->states;
    }

    public function setStates(?array $states): OrderPreferences
    {
        $this->states = $states;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(?bool $deleted): OrderPreferences
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getSettled(): ?bool
    {
        return $this->settled;
    }

    public function setSettled(?bool $settled): OrderPreferences
    {
        $this->settled = $settled;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'dateType' => $this->dateType->value,
            'columns' => $this->columns,
            'client' => $this->client?->getId(),
            'staff' => $this->staff?->getId(),
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'states' => $this->states,
            'deleted' => $this->deleted,
            'settled' => $this->settled,
        ];
    }
}
