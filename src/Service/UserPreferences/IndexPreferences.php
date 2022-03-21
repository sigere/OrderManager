<?php

namespace App\Service\UserPreferences;

use App\Entity\Client;
use App\Entity\Order;
use App\Entity\Staff;

class IndexPreferences extends AbstractPreferences
{
    public const COLUMNS = [
        'adoption',
        'client',
        'topic',
        'lang',
        'deadline',
        'staff'
    ];

    public const DATE_TYPE_DEADLINE = "deadline";
    public const DATE_TYPE_ADOPTION = "adoption";

    private array $states;
    private bool $deleted;
    private bool $settled;
    protected array $columns;
    protected ?Client $client;
    protected ?Staff $staff;
    protected string $dateType;
    protected ?\DateTime $dateFrom;
    protected ?\DateTime $dateTo;

    /**
     * @param mixed $data
     * @return void
     */
    public function applyForm(mixed $data): void
    {
        $this->setDateFrom($data['date-from'] ?? null);
        $this->setDateTo($data['date-to'] ?? null);
        $this->setStaff($data['select-staff'] ?? null);
        $this->setClient($data['select-client'] ?? null);

        if (isset($data['date-type']) &&
            in_array(
                $data['date-type'],
                [self::DATE_TYPE_DEADLINE, self::DATE_TYPE_ADOPTION]
            )
        ) {
            $this->setDateType($data['date-type']);
        }

        $states = [];
        foreach (Order::STATES as $STATE) {
            if (array_key_exists($STATE, $data) && $data[$STATE] === true) {
                $states[] = $STATE;
            }
        }
        $this->setStates($states);

        $columns = [];
        foreach (self::COLUMNS as $COLUMN) {
            if (array_key_exists($COLUMN, $data) && $data[$COLUMN] === true) {
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
        $this->dateTo= isset($config['date_to']) ? new \DateTime($config['date_to']['date']) : null;

        $this->dateType = ($config['date_type'] ?? "") == self::DATE_TYPE_ADOPTION
            ? self::DATE_TYPE_ADOPTION : self::DATE_TYPE_DEADLINE;

        $this->client = $this->entityManager
            ->getRepository(Client::class)
            ->findOneBy([
                'id' => ($config['client'] ?? 0)
            ]);

        $this->staff = $this->entityManager
            ->getRepository(Staff::class)
            ->findOneBy([
                'id' => ($config['staff'] ?? 0)
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

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param array $columns
     * @return IndexPreferences
     */
    public function setColumns(array $columns): IndexPreferences
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @return string
     */
    public function getDateType(): string
    {
        return $this->dateType;
    }

    /**
     * @param string $dateType
     * @return IndexPreferences
     */
    public function setDateType(string $dateType): IndexPreferences
    {
        $this->dateType = $dateType;
        return $this;
    }

    /**
     * @return array
     */
    public function getStates(): array
    {
        return $this->states;
    }

    /**
     * @param array $states
     * @return IndexPreferences
     */
    public function setStates(array $states): IndexPreferences
    {
        $this->states = $states;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     * @return IndexPreferences
     */
    public function setDeleted(bool $deleted): IndexPreferences
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSettled(): bool
    {
        return $this->settled;
    }

    /**
     * @param bool $settled
     * @return IndexPreferences
     */
    public function setSettled(bool $settled): IndexPreferences
    {
        $this->settled = $settled;
        return $this;
    }

    /**
     * @return Client|null
     */
    public function getClient(): ?Client
    {
        return $this->client;
    }

    /**
     * @param Client|null $client
     * @return IndexPreferences
     */
    public function setClient(?Client $client): IndexPreferences
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return Staff|null
     */
    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    /**
     * @param Staff|null $staff
     * @return IndexPreferences
     */
    public function setStaff(?Staff $staff): IndexPreferences
    {
        $this->staff = $staff;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateFrom(): ?\DateTime
    {
        return $this->dateFrom;
    }

    /**
     * @param \DateTime|null $dateFrom
     * @return IndexPreferences
     */
    public function setDateFrom(?\DateTime $dateFrom): IndexPreferences
    {
        $this->dateFrom = $dateFrom;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateTo(): ?\DateTime
    {
        return $this->dateTo;
    }

    /**
     * @param \DateTime|null $dateTo
     * @return IndexPreferences
     */
    public function setDateTo(?\DateTime $dateTo): IndexPreferences
    {
        $this->dateTo = $dateTo;
        return $this;
    }

    protected function getArrayKey(): string
    {
        return "index";
    }
}
