<?php

namespace App\Tests\Unit\Reports\CertifiedUaPlReport;

use App\Reports\CertifiedUaPlReport\CertifiedUaPlReport;
use App\Reports\Exception\MissingParameterException;
use App\Repository\OrderRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class CertifiedUaPlReportTest extends TestCase
{
    public function test_not_configured_report(): void
    {
        $report = new CertifiedUaPlReport(
            $this->createConfiguredMock(OrderRepository::class, []),
            $this->createConfiguredMock(TranslatorInterface::class, [])
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("not configured");

        $report->getData();
    }

    public function test_configure_throws_exception(): void
    {
        $report = new CertifiedUaPlReport(
            $this->createConfiguredMock(OrderRepository::class, []),
            $this->createConfiguredMock(TranslatorInterface::class, [])
        );

        $this->expectException(MissingParameterException::class);

        $report->configure(['dateFrom' => null, 'dateTo' => null]);
    }
}
