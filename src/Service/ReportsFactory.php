<?php

namespace App\Service;

use App\Repository\LangRepository;
use App\Repository\OrderRepository;
use App\Service\Reports\CertifiedUaPlReport;
use JetBrains\PhpStorm\Pure;

class ReportsFactory
{
    const REPORTS = [
        [
            'id' => 'CERTIFIED_UA_PL',
            'name' => 'Albert',
            'description' => 'Tłumaczenia przysięgłe pl->ua i ua->pl',
        ]
    ];

    public function __construct(
        private OrderRepository $orderRepository,
        private LangRepository $langRepository
    ){
    }

    #[Pure] public function getReportService(string $report) : ?ReportInterface
    {
        return match ($report) {
            self::REPORTS[0]['id'] => new CertifiedUaPlReport($this->langRepository, $this->orderRepository),
            default => null
        };
    }
}