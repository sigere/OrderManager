<?php

declare(strict_types=1);

namespace App\Controller\Order;

use App\Form\Model\OrdersSearch;
use App\Form\OrdersSearchForm;
use App\Repository\OrderRepository;
use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ORDER_READ')]
#[Route('/api/order', condition: 'request.isXmlHttpRequest()', format: 'json')]
class ApiSearchController extends AbstractController
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly OrderService $orderService,
    ) {
    }

    #[Route('/search', name: 'api_order_search_get', methods: ['GET'])]
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

    #[Route('/search', name: 'api_order_search_post', methods: ['POST'])]
    public function searchPostAction(Request $request): Response
    {
        $form = $this->createForm(OrdersSearchForm::class);
        $form->handleRequest($request);

        $orders = [];
        $found = 0;
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var OrdersSearch $ordersSearch */
            $ordersSearch = $form->getData();
            $orders = $this->orderRepository->getByOrdersSearch($ordersSearch, $found);

            if (!$orders) {
                $form->addError(new FormError('No orders found.'));
            }

            return new JsonResponse([
                'success' => $found > 0,
                'data' => [
                    'renderedSearch' => $this->orderService->renderSearchForm($form, $orders, $found),
                ],
            ], $found > 0 ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'success' => true,
            'data' => [
                'renderedSearch' => $this->orderService->renderSearchForm($form, $orders, $found),
            ],
        ]);
    }
}
