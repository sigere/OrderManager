<?php

namespace App\Service\Reports;

use Twig\Environment;
use App\Entity\Order;
use App\Repository\LangRepository;
use App\Repository\OrderRepository;
use App\Service\ReportInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class CertifiedUaPlReport implements ReportInterface
{
    private array $config = [];

    public function __construct (
        private LangRepository $langRepository,
        private OrderRepository $orderRepository,
        private Environment $twig
    ) {
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderForm(): string
    {
        return $this->twig->render('reports/forms/CERTIFIED_UA_PL.html.twig');
    }

    /**
     * @throws Exception
     */
    public function configure(Request $request): void
    {
        $from = $request->get('from');
        if ($from) {
            $this->config['from'] = new \DateTime($from);
        }

        $to = $request->get('to');
        if ($to) {
            $this->config['to'] = new \DateTime($to);
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getPreview(): array
    {
        if (!isset($this->config)) {
            throw new Exception('Report not configured.');
        }
        return $this->getArray();
    }


    /**
     * @return string
     * @throws Exception
     */
    public function export(): string
    {
        if (!isset($this->config)) {
            throw new Exception('Report not configured.');
        }

        $array = $this->getArray();

        $filename = uniqid() . '.xlsx';
        $path = '../var/tmp/' . $filename;
        if (!file_exists('../var/tmp')) {
            mkdir('../var/tmp', 775);
        }
        \SimpleXLSXGen::fromArray($array)->saveAs($path);
        return $filename;
    }

    private function getOrders()
    {
        $ua = $this->langRepository->findOneBy(['short' => 'UA']);
        $pl = $this->langRepository->findOneBy(['short' => 'PL']);

        $queryBuilder = $this->orderRepository->createQueryBuilder('o');
        $queryBuilder = $queryBuilder
            ->andWhere('o.deletedAt is null')
            ->andWhere('o.certified = 1')
            ->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('o.baseLang', ':pl'),
                        $queryBuilder->expr()->eq('o.targetLang', ':ua')
                    ),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('o.baseLang', ':ua'),
                        $queryBuilder->expr()->eq('o.targetLang', ':pl')
                    )
                )
            )
            ->setParameter('ua', $ua)
            ->setParameter('pl', $pl)
            ->setMaxResults(1000);

        if (isset($this->config['from']) && $this->config['from']) {
            $queryBuilder = $queryBuilder
                ->andWhere('o.deadline > :from')
                ->setParameter('from', $this->config['from']);
        }

        if (isset($this->config['to']) && $this->config['to']) {
            $queryBuilder = $queryBuilder
                ->andWhere('o.deadline < :to')
                ->setParameter('to', $this->config['to']);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    private function getArray() : array
    {
        $orders = $this->getOrders();

        $table = [];
        $sumOfNetto = 0;

        /** @var Order $order */
        foreach ($orders as $order) {
            $table[] = [
                $order->getId(),
                $order->getAdoption()->format('d.m.Y h:i'),
                $order->getDeadline()->format('d.m.Y h:i'),
                (string)$order->getClient(),
                $order->getTopic(),
                $order->getInfo(),
                (string)$order->getStaff(),
                (string)$order->getBaseLang(),
                (string)$order->getTargetLang(),
                $order->getCertified() ? 'tak' : 'nie',
                $order->getPages(),
                $order->getPrice(),
                $order->getNetto(),
                (string)$order->getState()
            ];
            $sumOfNetto += $order->getNetto();
        }
        $header = [];
        $header[] = array_merge(array_fill(0, 12, ''), [$sumOfNetto, '']);
        $header[] = [
            'Id',
            'Wprowadzono',
            'Termin',
            'Klient',
            'Temat',
            'Notatki',
            'Wykonawca',
            'Z',
            'Na',
            'UW',
            'L_str',
            'Cena',
            'Netto',
            'Status'
        ];

        return array_merge($header,$table);
    }
}