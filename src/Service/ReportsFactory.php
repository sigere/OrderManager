<?php

namespace App\Service;

use App\Repository\LangRepository;
use App\Repository\OrderRepository;
use App\Service\Reports\CertifiedUaPlReport;
use JetBrains\PhpStorm\Pure;

class ReportsFactory
{
    const CERTIFIED_UA_PL = 1;

    public function __construct(
        private OrderRepository $orderRepository,
        private LangRepository $langRepository
    ){
    }

    #[Pure] public function getReportService(int $report) : ?ReportInterface
    {
        return match ($report) {
            self::CERTIFIED_UA_PL => new CertifiedUaPlReport($this->langRepository, $this->orderRepository),
            default => null
        };
    }
}