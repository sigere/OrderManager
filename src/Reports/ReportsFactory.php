<?php

namespace App\Reports;

class ReportsFactory
{
    public function __construct(
        private array $reports
    ) {
    }

    public function getAvailableReports() : array
    {
        $result = [];
        /** @var AbstractReport $report */
        foreach ($this->reports as $report) {
            $result[$report->getName()] = $report->getNameForUI();
        }

        return $result;
    }

    public function getReport(string $name) : ?AbstractReport
    {
        /** @var AbstractReport $report */
        foreach ($this->reports as $report) {
            if ($report->getName() === $name) {
                return $report;
            }
        }

        return null;
    }
}
