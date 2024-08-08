<?php

declare(strict_types=1);

namespace App\Controller\Orders;

use App\Entity\User;
use App\Preferences\Form\OrderFiltersForm;
use App\Repository\LogRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

#[Route('/orders', name: 'orders', methods: ['GET'])]
class IndexController extends AbstractController
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly LogRepository $logRepository,
        private readonly RouterInterface $router,
    ) {
    }

    public function __invoke(#[MapQueryParameter] ?int $order = null): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $orders = $this->orderRepository->getByOrdersPreferences(
            $user->getPreferences()->getOrdersPreferences(), $rowsCount
        );

        $orderEntity = null;
        $logs = [];
        if ($order) {
            $orderEntity = $this->orderRepository->findOneBy(['id' => $order]);
            $logs = $this->logRepository->findBy(
                ['order' => $order], ['createdAt' => 'DESC'], 100
            );
        }

        $form = $this->createForm(
            OrderFiltersForm::class, $user->getPreferences()->getOrdersPreferences()
        );

        $endpointData = [
            'data-url' => $this->router->generate(
                name: 'api_orders_table', referenceType: UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'data-method' => 'GET',
        ];

        // $options = $order ? $this->optionsProviderFactory->getOptions($order) : [];

        return $this->render('orders/index.html.twig', [
            'orders' => $orders,
            'details' => [
                'order' => $orderEntity,
                'logs' => $logs,
            ],
            'filtersForm' => $form->createView(),
            'preferences' => $user->getPreferences()->getOrdersPreferences(),
            'options' => [], // todo $options
            'rowsFound' => $rowsCount,
            'rowsShown' => min($rowsCount, OrderRepository::LIMIT),
            'endpointData' => $endpointData,
        ]);
    }
}
