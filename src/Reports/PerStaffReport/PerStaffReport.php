<?php

namespace App\Reports\PerStaffReport;

use App\Entity\Order;
use App\Reports\AbstractReport;
use App\Reports\Exception\MissingParameterException;
use App\Repository\OrderRepository;
use DateTime;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

class PerStaffReport extends AbstractReport
{
    public const NAME = "per_staff";
    public const NAME_FOR_UI = "Dla pracownika";

    public function __construct(
        private OrderRepository $orderRepository,
        private TranslatorInterface $translator
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
        return PerStaffReportForm::class;
    }

    public function configure(mixed $data): void
    {
        if (!isset($data['dateFrom']) ||
            !isset($data['dateTo']) ||
            !isset($data['staff'])) {
            throw new MissingParameterException();
        }

        $from = $data['dateFrom'];
        if ($from) {
            $this->config['dateFrom'] = $from;
        }

        $to = $data['dateTo'];
        if ($to) {
            /** @var DateTime $to */
            $to->modify('+1 day');
            $this->config['dateTo'] = $to;
        }

        $this->config['staff'] = $data['staff'];
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
        $orders = $this->getOrders();

        $table = [];
        $sumOfNetto = 0;

        /** @var Order $order */
        foreach ($orders as $order) {
            $table[] = [
                $order->getId(),
                $order->getDeadline()->format('d.m.Y h:i'),
                (string)$order->getClient(),
                $order->getTopic(),
                (string)$order->getStaff(),
                (string)$order->getBaseLang(),
                (string)$order->getTargetLang(),
                $order->getCertified() ? 'tak' : 'nie',
                $order->getPages(),
                $order->getPrice(),
                $order->getNetto(),
                $this->translator->trans((string)$order->getState())
            ];
            $sumOfNetto += $order->getNetto();
        }
        $header = [];
        $header[] = array_merge(array_fill(0, 10, ''), [$sumOfNetto, '']);
        $header[] = [
            'Id',
            'Termin',
            'Klient',
            'Temat',
            'Wykonawca',
            'Z',
            'Na',
            'UW',
            'L_str',
            'Cena',
            'Netto',
            'Status'
        ];

        return array_merge($header, $table);
    }

    private function getOrders(): array
    {
        return $this->orderRepository
            ->createQueryBuilder('o')
            ->select('o, c, bl, tl')
            ->innerJoin('o.client', 'c')
            ->innerJoin('o.baseLang', 'bl')
            ->innerJoin('o.targetLang', 'tl')
            ->andWhere('o.staff = :staff')
            ->andWhere('o.deletedAt is null')
            ->andWhere('o.deadline >= :dateFrom')
            ->andWhere('o.deadline <= :dateTo')
            ->setParameter('staff', $this->config['staff'])
            ->setParameter('dateFrom', $this->config['dateFrom'])
            ->setParameter('dateTo', $this->config['dateTo'])
            ->getQuery()
            ->getResult();
    }

    public function getRowsCount(): int
    {
        return $this->orderRepository
            ->createQueryBuilder('o')
            ->select('count(o.id)')
            ->andWhere('o.staff = :staff')
            ->andWhere('o.deletedAt is null')
            ->andWhere('o.deadline >= :dateFrom')
            ->andWhere('o.deadline <= :dateTo')
            ->setParameter('staff', $this->config['staff'])
            ->setParameter('dateFrom', $this->config['dateFrom'])
            ->setParameter('dateTo', $this->config['dateTo'])
            ->getQuery()
            ->getSingleScalarResult();
    }
}
