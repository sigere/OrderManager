<?php

namespace App\Service\UserPreferences;

use App\Form\ArchivesFiltersForm;

class ArchivesPreferences extends AbstractOrderPreferences
{
    private bool $deleted;

    protected function getArrayKey(): string
    {
        return "archives";
    }

    protected function encode(): array
    {
        return array_merge(parent::encode(), [
            'deleted' => $this->deleted,
        ]);
    }

    /**
     * @param mixed $data
     * @return void
     */
    public function applyForm(mixed $data): void
    {
        parent::applyForm($data);

        if (isset($data['deleted'])) {
            $this->setDeleted($data['deleted'] === true);
        }

        $columns = [];
        foreach (ArchivesFiltersForm::COLUMNS as $COLUMN) {
            if (array_key_exists($COLUMN, $data) && $data[$COLUMN] === true) {
                $columns[] = $COLUMN;
            }
        }
        $this->setColumns($columns);

        $this->save();
    }

    protected function load(array $config): void
    {
        parent::load($config);
        $this->deleted = isset($config['deleted']) && $config['deleted'] === true;
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
     * @return ArchivesPreferences
     */
    public function setDeleted(bool $deleted): ArchivesPreferences
    {
        $this->deleted = $deleted;
        return $this;
    }
}
