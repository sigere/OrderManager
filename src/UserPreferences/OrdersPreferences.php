<?php

declare(strict_types=1);

namespace App\UserPreferences;

use App\Entity\Client;
use App\Entity\Order;
use App\Entity\Staff;

class OrdersPreferences extends AbstractPreferences
{
    public const array COLUMNS = [
        'adoption',
        'client',
        'topic',
        'lang',
        'deadline',
        'staff',
    ];

    public const string DATE_TYPE_DEADLINE = 'deadline';
    public const string DATE_TYPE_ADOPTION = 'adoption';

    protected array $columns;
    protected ?Client $client;
    protected ?Staff $staff;
    protected string $dateType;
    protected ?\DateTime $dateFrom;
    protected ?\DateTime $dateTo;
    private array $states;
    private bool $deleted;
    private bool $settled;

    public function applyForm(mixed $data): void
    {
        $this->setDateFrom($data['date-from'] ?? null);
        $this->setDateTo($data['date-to'] ?? null);
        $this->setStaff($data['select-staff'] ?? null);
        $this->setClient($data['select-client'] ?? null);

        if (isset($data['date-type']) && in_array(
            $data['date-type'], [self::DATE_TYPE_DEADLINE, self::DATE_TYPE_ADOPTION]
        )) {
            $this->setDateType($data['date-type']);
        }

        $states = [];
        foreach (Order::STATES as $STATE) {
            if (array_key_exists($STATE, $data) && true === $data[$STATE]) {
                $states[] = $STATE;
            }
        }
        $this->setStates($states);

        $columns = [];
        foreach (self::COLUMNS as $COLUMN) {
            if (array_key_exists($COLUMN, $data) && true === $data[$COLUMN]) {
                $columns[] = $COLUMN;
            }
        }
        $this->setColumns($columns);

        $this->setDeleted($data['deleted'] ?? null);
        $this->setSettled($data['settled'] ?? null);

        $this->save();
    }

    protected function decode(array $config): void
    {
        $this->columns = $config['columns'] ?? [];

        $this->dateFrom = isset($config['date_from']) ? new \DateTime($config['date_from']['date']) : null;
        $this->dateTo = isset($config['date_to']) ? new \DateTime($config['date_to']['date']) : null;

        $this->dateType = ($config['date_type'] ?? '') == self::DATE_TYPE_ADOPTION ? self::DATE_TYPE_ADOPTION : self::DATE_TYPE_DEADLINE;

        $this->client = $this->entityManager->getRepository(Client::class)->findOneBy([
            'id' => ($config['client'] ?? 0),
        ]);

        $this->staff = $this->entityManager->getRepository(Staff::class)->findOneBy([
            'id' => ($config['staff'] ?? 0),
        ]);

        $this->states = $config['states'] ?? [];
        $this->deleted = $config['deleted'];
        $this->settled = $config['settled'];
    }

    protected function encode(): array
    {
        return [
            'states' => $this->states,
            'deleted' => $this->deleted,
            'settled' => $this->settled,
            'columns' => $this->columns,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'date_type' => $this->dateType,
            'client' => $this->client?->getId(),
            'staff' => $this->staff?->getId(),
        ];
    }

    protected function getArrayKey(): string
    {
        return 'index';
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function setColumns(array $columns): OrdersPreferences
    {
        $this->columns = $columns;

        return $this;
    }

    public function getDateType(): string
    {
        return $this->dateType;
    }

    public function setDateType(string $dateType): OrdersPreferences
    {
        $this->dateType = $dateType;

        return $this;
    }

    public function getStates(): array
    {
        return $this->states;
    }

    public function setStates(array $states): OrdersPreferences
    {
        $this->states = $states;

        return $this;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): OrdersPreferences
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getSettled(): bool
    {
        return $this->settled;
    }

    public function setSettled(bool $settled): OrdersPreferences
    {
        $this->settled = $settled;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): OrdersPreferences
    {
        $this->client = $client;

        return $this;
    }

    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    public function setStaff(?Staff $staff): OrdersPreferences
    {
        $this->staff = $staff;

        return $this;
    }

    public function getDateFrom(): ?\DateTime
    {
        return $this->dateFrom;
    }

    public function setDateFrom(?\DateTime $dateFrom): OrdersPreferences
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    public function getDateTo(): ?\DateTime
    {
        return $this->dateTo;
    }

    public function setDateTo(?\DateTime $dateTo): OrdersPreferences
    {
        $this->dateTo = $dateTo;

        return $this;
    }
}
