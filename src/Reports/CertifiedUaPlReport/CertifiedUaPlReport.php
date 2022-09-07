<?php

namespace App\Reports\CertifiedUaPlReport;

use App\Entity\Order;
use App\Reports\AbstractReport;
use App\Reports\Exception\MissingParameterException;
use App\Repository\OrderRepository;
use DateTime;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

class CertifiedUaPlReport extends AbstractReport
{
    public const NAME = "certified_ua_pl";
    public const NAME_FOR_UI = "Przysięgłe UA/PL";

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

    /**
     * @return string
     */
    public function getFormFQCN(): string
    {
        return CertifiedUaPlReportForm::class;
    }

    /**
     * @throws Exception
     */
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
            /** @var DateTime $to */
            $to->modify('+1 day');
            $this->config['dateTo'] = $to;
        }
    }

    /**
     * @param int|null $limit
     * @return array
     * @throws Exception
     */
    public function getData(?int $limit = null): array
    {
        if (!isset($this->config)) {
            throw new Exception('Report not configured.');
        }
        return $this->getArray($limit);
    }

    private function getOrders()
    {
        $queryBuilder = $this->orderRepository->createQueryBuilder('o');
        return $queryBuilder
            ->select('o, c, bl, tl, s')
            ->innerJoin('o.client', 'c')
            ->innerJoin('o.baseLang', 'bl')
            ->innerJoin('o.targetLang', 'tl')
            ->innerJoin('o.staff', 's')
            ->andWhere('o.deletedAt is null')
            ->andWhere('o.certified = 1')
            ->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('bl.short', ':pl'),
                        $queryBuilder->expr()->eq('tl.short', ':ua')
                    ),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('bl.short', ':ua'),
                        $queryBuilder->expr()->eq('tl.short', ':pl')
                    )
                )
            )
            ->andWhere('o.deadline > :from')
            ->andWhere('o.deadline < :to')
            ->setParameter('from', $this->config['dateFrom'])
            ->setParameter('to', $this->config['dateTo'])
            ->setParameter('ua', 'UA')
            ->setParameter('pl', 'PL')
            ->setMaxResults(1000)
            ->getQuery()
            ->getResult();
    }

    private function getArray(?int $limit) : array
    {
        $orders = $this->getOrders();
        $limit = $limit === null ? PHP_INT_MAX : $limit;

        $table = [];
        $sumOfNetto = 0;

        $i = 0;
        /** @var Order $order */
        foreach ($orders as $order) {
            $sumOfNetto += $order->getNetto();

            if ($i++ <= $limit) {
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
            }
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
            'L. str.',
            'Cena',
            'Netto',
            'Status'
        ];

        return array_merge($header, $table);
    }

    public function getRowsCount(): int
    {
        $queryBuilder = $this->orderRepository->createQueryBuilder('o');
        return $queryBuilder
            ->select('COUNT(o.id)')
            ->innerJoin('o.baseLang', 'bl')
            ->innerJoin('o.targetLang', 'tl')
            ->andWhere('o.deletedAt is null')
            ->andWhere('o.certified = 1')
            ->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('bl.short', ':pl'),
                        $queryBuilder->expr()->eq('tl.short', ':ua')
                    ),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('bl.short', ':ua'),
                        $queryBuilder->expr()->eq('tl.short', ':pl')
                    )
                )
            )
            ->andWhere('o.deadline > :from')
            ->andWhere('o.deadline < :to')
            ->setParameter('from', $this->config['dateFrom'])
            ->setParameter('to', $this->config['dateTo'])
            ->setParameter('ua', 'UA')
            ->setParameter('pl', 'PL')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
