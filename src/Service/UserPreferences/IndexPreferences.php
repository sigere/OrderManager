<?php

namespace App\Service\UserPreferences;

use App\Entity\Order;

class IndexPreferences extends AbstractOrderPreferences
{
    private array $states;
    private bool $deleted;
    private bool $settled;

    protected function getArrayKey(): string
    {
        return "index";
    }

    /**
     * @param mixed $data
     * @return void
     */
    public function applyForm(mixed $data): void
    {
        parent::applyForm($data);

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

    protected function load(array $config): void
    {
        parent::load($config);
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
}
