<?php

declare(strict_types=1);

namespace App\Service\UserPreferences;

use App\Entity\Staff;

class RepertoryPreferences extends AbstractPreferences
{
    private ?int $month;
    private ?int $year;
    private ?Staff $staff;

    protected function getArrayKey(): string
    {
        return "repertory";
    }

    protected function encode(): array
    {
        return [
            "month" => $this->month,
            "year" => $this->year,
            "staff" => $this->staff?->getId()
        ];
    }

    protected function decode(array $config): void
    {
        $this->month = $config["month"] ?? null;
        $this->year = $config["year"] ?? null;
        $this->staff = $this->entityManager
            ->getRepository(Staff::class)
            ->findOneBy([
                'id' => ($config['staff'] ?? 0)
            ]);
    }

    public function applyForm(mixed $data): void
    {
        $this->year = $data["year"] ?? null;
        $this->month = $data["month"] ?? null;
        $this->staff = $data['select-staff'] ?? null;
        $this->save();
    }

    /**
     * @return int|null
     */
    public function getMonth(): ?int
    {
        return $this->month;
    }

    /**
     * @param int|null $month
     * @return RepertoryPreferences
     */
    public function setMonth(?int $month): RepertoryPreferences
    {
        $this->month = $month;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getYear(): ?int
    {
        return $this->year;
    }

    /**
     * @param int|null $year
     * @return RepertoryPreferences
     */
    public function setYear(?int $year): RepertoryPreferences
    {
        $this->year = $year;
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
     * @return RepertoryPreferences
     */
    public function setStaff(?Staff $staff): RepertoryPreferences
    {
        $this->staff = $staff;
        return $this;
    }
}
