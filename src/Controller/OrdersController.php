<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class OrdersController extends AbstractController
{
    public function __construct(
       private readonly Environment $environment,
    ) {
    }

    #[Route('/orders', name: 'orders', methods: ['GET'])]
    public function indexAction(): Response
    {
//        $orders = $this->orderRepository->getByIndexPreferences($this->preferences, $rowsCount);
//        $order = $this->orderRepository->findOneBy(['id' => $request->get('order')]);
//        $logs = $this->logRepository->findBy(
//            ['order' => $order],
//            ['createdAt' => 'DESC'],
//            100
//        );
//        $form = $this->createForm(IndexFiltersForm::class);
//        $options = $order ? $this->optionsProviderFactory->getOptions($order) : [];
//
//        return $this->render('index/index.html.twig', [
//            'orders' => $orders,
//            'details' => [
//                'order' => $order,
//                'logs' => $logs
//            ],
//            'filtersForm' => $form->createView(),
//            'preferences' => $this->preferences,
//            'options' => $options,
//            'rowsFound' => $rowsCount,
//            'rowsShown' => min($rowsCount, $this->orderRepository::LIMIT),
//            'dataSourceUrl' => '/order'
//        ]);

        return $this->render('orders/index.html.twig', [
            'orders' => [],
            'details' => [
                'order' => [],
                'logs' => [],
            ],
//            'filtersForm' => $form->createView(),
            'preferences' => [],
            'options' => [],
            'rowsFound' => 0,
            'rowsShown' => 0,
            'dataSourceUrl' => '/order'
        ]);
    }
}