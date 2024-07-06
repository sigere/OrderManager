<?php

declare(strict_types=1);

namespace App\Service\UserPreferences;

class InvoicesPreferences extends AbstractPreferences
{
    private ?int $month;
    private ?int $year;
    private ?\DateTime $issueDate;
    private ?\DateTime $paymentDate;

    protected function getArrayKey(): string
    {
        return "invoices";
    }

    protected function encode(): array
    {
        return [
            "month" => $this->month,
            "year" => $this->year,
            "issue_date" => $this->issueDate,
            "payment_date" => $this->paymentDate
        ];
    }

    protected function decode(array $config): void
    {
        $this->month = $config["month"] ?? null;
        $this->year = $config["year"] ?? null;
        $this->issueDate = isset($config['issue_date']) ? new \DateTime($config['issue_date']['date']) : null;
        $this->paymentDate = isset($config['payment_date']) ? new \DateTime($config['payment_date']['date']) : null;
    }

    public function applyForm(mixed $data): void
    {
        $this->year = $data["year"] ?? null;
        $this->month = $data["month"] ?? null;
        $this->issueDate = $data['issue-date'] ?? null;
        $this->paymentDate = $data['payment-date'] ?? null;

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
     * @return InvoicesPreferences
     */
    public function setMonth(?int $month): InvoicesPreferences
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
     * @return InvoicesPreferences
     */
    public function setYear(?int $year): InvoicesPreferences
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getIssueDate(): ?\DateTime
    {
        return $this->issueDate;
    }

    /**
     * @param \DateTime|null $issueDate
     * @return InvoicesPreferences
     */
    public function setIssueDate(?\DateTime $issueDate): InvoicesPreferences
    {
        $this->issueDate = $issueDate;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getPaymentDate(): ?\DateTime
    {
        return $this->paymentDate;
    }

    /**
     * @param \DateTime|null $paymentDate
     * @return InvoicesPreferences
     */
    public function setPaymentDate(?\DateTime $paymentDate): InvoicesPreferences
    {
        $this->paymentDate = $paymentDate;
        return $this;
    }
}
