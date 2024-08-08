<?php
declare(strict_types=1);

namespace App\Controller\Orders;

use App\Controller\ApiControllerInterface;
use App\Entity\Enum\OrderState;
use App\Entity\Order;
use App\Entity\User;
use App\Form\Model\OrdersSearch;
use App\Form\OrdersSearchForm;
use App\Repository\OrderRepository;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Twig\Environment;

#[Route('/api/orders', condition: 'request.isXmlHttpRequest()', format: 'json')]
class ApiController extends AbstractController implements ApiControllerInterface
{
    public function __construct(
       private readonly OrderRepository $orderRepository,
       private readonly OrderService $orderService,
       private readonly RouterInterface $router,
       private readonly Environment $twig,
       private readonly EntityManagerInterface $entityManager,
       private readonly CacheInterface $cache,
    ) {
    }
    #[Route('/table', name: 'api_orders_table', methods: ['GET'])]
    public function tableAction(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $orders = $this->orderRepository->getByOrdersPreferences(
            $user->getPreferences()->getOrdersPreferences(), $rowsCount
        );

        $endpointData = [
            'data-url' => $this->router->generate(
                name: 'api_orders_table', referenceType: UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'data-method' => 'GET',
        ];

        $renderedTable = $this->twig->render('orders/_table.html.twig', [
            'orders' => $orders,
            'preferences' => $user->getPreferences()->getOrdersPreferences(),
            'endpointData' => $endpointData,
        ]);

        $renderedRowsCount = $this->twig->render('rows_count.html.twig', [
            'rowsFound' => $rowsCount,
            'rowsShown' => min($rowsCount, OrderRepository::LIMIT),
        ]);

        return new JsonResponse([
            'success' => true,
            'data' => [
                'renderedTable' => $renderedTable,
                'renderedRowsCount' => $renderedRowsCount,
            ],
        ]);
    }

    #[Route('/{id}/state', name: 'api_orders_state_put', methods: ['PUT'])]
    public function orderStatePutAction(Order $order, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $order->setState(OrderState::from($request->get('state')));
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $renderedRow = $this->twig->render('orders/_table_row.html.twig', [
            'order' => $order,
            'preferences' => $user->getPreferences()->getOrdersPreferences(),
        ]);

        return new JsonResponse([
            'success' => true,
            'data' => [
                'renderedRow' => $renderedRow,
            ],
        ]);
    }

    #[Route('/search', name: 'api_orders_search_get', methods: ['GET'])]
    public function searchGetAction(): Response
    {
        $form = $this->createForm(OrdersSearchForm::class);

        return new JsonResponse([
            'success' => true,
            'data' => [
                'renderedSearch' => $this->orderService->renderSearchForm($form),
            ],
        ]);
    }

    #[Route('/search', name: 'api_orders_search_post', methods: ['POST'])]
    public function searchPostAction(Request $request): Response
    {
        $form = $this->createForm(OrdersSearchForm::class);
        $form->handleRequest($request);

        $orders = [];
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var OrdersSearch $ordersSearch */
            $ordersSearch = $form->getData();
            $orders = $this->orderRepository->getByOrdersSearch($ordersSearch);
            $orders = is_array($orders) ? $orders : [$orders];
        }

        return new JsonResponse([
            'success' => true,
            'data' => [
                'renderedSearch' => $this->orderService->renderSearchForm($form, $orders),
            ],
        ]);
    }
}