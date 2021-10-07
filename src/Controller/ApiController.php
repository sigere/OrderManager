<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\LangRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    public function __construct(
        private OrderRepository $orderRepository,
        private LangRepository $langRepository
    ) {
    }

    /**
     * @return Response
     * @Route("/api/xlsExport", name="api_xlx_export")
     */
    public function xlsExport() : Response
    {
        $orders = $this->getOrders();

        $table = [];
        $sumOfNetto = 0;

        /** @var Order $order */
        foreach ($orders as $order) {
            $table[] = [
                $order->getId(),
                $order->getDeadline(),
                $order->getAdoption(),
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

        $result = array_merge($header,$table);
//        dd($result);
//        \SimpleXLSXGen::fromArray($result, 'TestXLS')->saveAs('./var/testxls.xlsx');
        return new BinaryFileResponse(\SimpleXLSXGen::fromArray($result, 'TestXLS')->download());
//        return new JsonResponse(['success' => true, 'orders' => $this->getOrders()]);
    }

    private function getOrders()
    {
        $ua = $this->langRepository->findOneBy(['short' => 'UA']);
        $pl = $this->langRepository->findOneBy(['short' => 'PL']);

        $queryBuilder = $this->orderRepository->createQueryBuilder('o');
        return $queryBuilder
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
            ->getQuery()
            ->getResult();
    }
}
