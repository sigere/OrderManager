<?php

declare(strict_types=1);

namespace App\UserPreferences;

use App\Entity\Staff;

class RepertoryPreferences extends AbstractPreferences
{
    private ?int $month;
    private ?int $year;
    private ?Staff $staff;

    protected function getArrayKey(): string
    {
        return 'repertory';
    }

    protected function encode(): array
    {
        return [
            'month' => $this->month,
            'year' => $this->year,
            'staff' => $this->staff?->getId(),
        ];
    }

    protected function decode(array $config): void
    {
        $this->month = $config['month'] ?? null;
        $this->year = $config['year'] ?? null;
        $this->staff = $this->entityManager
            ->getRepository(Staff::class)
            ->findOneBy([
                'id' => ($config['staff'] ?? 0),
            ]);
    }

    public function applyForm(mixed $data): void
    {
        $this->year = $data['year'] ?? null;
        $this->month = $data['month'] ?? null;
        $this->staff = $data['select-staff'] ?? null;
        $this->save();
    }

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(?int $month): RepertoryPreferences
    {
        $this->month = $month;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): RepertoryPreferences
    {
        $this->year = $year;

        return $this;
    }

    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    public function setStaff(?Staff $staff): RepertoryPreferences
    {
        $this->staff = $staff;

        return $this;
    }
}
