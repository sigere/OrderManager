<?php
declare(strict_types=1);

namespace App\UserPreferences;

use DateTime;

class InvoicesPreferences extends AbstractPreferences
{
    private ?int $month;
    private ?int $year;
    private ?DateTime $issueDate;
    private ?DateTime $paymentDate;

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(?int $month): InvoicesPreferences
    {
        $this->month = $month;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): InvoicesPreferences
    {
        $this->year = $year;

        return $this;
    }

    public function getIssueDate(): ?DateTime
    {
        return $this->issueDate;
    }

    public function setIssueDate(?DateTime $issueDate): InvoicesPreferences
    {
        $this->issueDate = $issueDate;

        return $this;
    }

    public function getPaymentDate(): ?DateTime
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(?DateTime $paymentDate): InvoicesPreferences
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

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
            "payment_date" => $this->paymentDate,
        ];
    }

    protected function decode(array $config): void
    {
        $this->month = $config["month"] ?? null;
        $this->year = $config["year"] ?? null;
        $this->issueDate = isset($config['issue_date']) ? new DateTime($config['issue_date']['date']) : null;
        $this->paymentDate = isset($config['payment_date']) ? new DateTime($config['payment_date']['date']) : null;
    }

    public function applyForm(mixed $data): void
    {
        $this->year = $data["year"] ?? null;
        $this->month = $data["month"] ?? null;
        $this->issueDate = $data['issue-date'] ?? null;
        $this->paymentDate = $data['payment-date'] ?? null;

        $this->save();
    }
}
