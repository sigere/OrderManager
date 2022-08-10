<?php

namespace App\Reports\PerEachStaffReport;

use App\Reports\AbstractReport;
use App\Reports\Exception\MissingParameterException;
use App\Repository\OrderRepository;
use Exception;

class PerEachStaffReport extends AbstractReport
{
    public const NAME = "per_each_staff";
    public const NAME_FOR_UI = "Dla wszystkich pracowników";

    public function __construct(
        private OrderRepository $orderRepository
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getNameForUI(): string
    {
        return self::NAME_FOR_UI;
    }

    public function getFormFQCN(): string
    {
        return PerEachStaffReportForm::class;
    }

    public function configure(mixed $data): void
    {
        if (!isset($data['dateFrom']) ||
            !isset($data['dateTo'])) {
            throw new MissingParameterException();
        }

        $from = $data['dateFrom'];
        if ($from) {
            $this->config['dateFrom'] = $from;
        }

        $to = $data['dateTo'];
        if ($to) {
            $this->config['dateTo'] = $to;
        }
    }

    public function getData(): array
    {
        if (!isset($this->config)) {
            throw new Exception('Report not configured.');
        }

        return $this->getArray();
    }

    private function getArray(): array
    {
        $array = $this->orderRepository
            ->createQueryBuilder('o')
            ->select(
                "CONCAT(s.firstName, s.lastName) as Pracownik",
                "COUNT(o.id) as Zlecenia",
                "SUM(ROUND(o.pages * o.price)) as Netto",
                "SUM(o.pages) as Strony"
            )
            ->innerJoin('o.staff', 's')
            ->groupBy('Pracownik')
            ->andWhere('o.deletedAt is null')
            ->andWhere('o.deadline >= :dateFrom')
            ->andWhere('o.deadline <= :dateTo')
            ->setParameter('dateFrom', $this->config['dateFrom'])
            ->setParameter('dateTo', $this->config['dateTo'])
            ->getQuery()
            ->getResult();

        $table = [];
        $table[] = [
            'Pracownik',
            'Zlecenia',
            'Netto',
            'Strony'
        ];

        return array_merge($table, $array);
    }
}
