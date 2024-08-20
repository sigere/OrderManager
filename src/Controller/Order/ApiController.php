<?php

declare(strict_types=1);

namespace App\Controller\Order;

use App\Controller\ApiControllerInterface;
use App\Entity\Enum\OrderState;
use App\Entity\Order;
use App\Entity\User;
use App\Form\OrderForm;
use App\Repository\OrderRepository;
use App\Service\FormErrorsFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[IsGranted('ROLE_ORDER_READ')]
#[Route('/api/order', condition: 'request.isXmlHttpRequest()', format: 'json')]
class ApiController extends AbstractController implements ApiControllerInterface
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly RouterInterface $router,
        private readonly Environment $twig,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly FormErrorsFormatter $formErrorsFormatter,
    ) {
    }

    #[Route('/table', name: 'api_order_table', methods: ['GET'])]
    public function tableAction(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $orders = $this->orderRepository->getByOrderPreferences(
            $user->getPreferences()->getOrdersPreferences(), $rowsCount
        );

        $endpointData = [
            'data-url' => $this->router->generate(
                name: 'api_order_table', referenceType: UrlGeneratorInterface::ABSOLUTE_URL
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

    #[IsGranted('ROLE_ORDER_UPDATE')]
    #[Route('/{id}/state', name: 'api_order_state_put', methods: ['PUT'])]
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

    #[IsGranted('ROLE_ORDER_CREATE')]
    #[Route('', name: 'api_order_post', methods: ['POST'])]
    public function postAction(Request $request): Response
    {
        $form = $this->createForm(OrderForm::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            $renderedForm = $this->twig->render('orders/_form.html.twig', [
                'form' => $form->createView(),
            ]);

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'renderedForm' => $renderedForm,
                ],
            ]);
        }

        if (!$form->isValid()) {
            $renderedForm = $this->twig->render('orders/_form.html.twig', [
                'form' => $form->createView(),
            ]);

            return new JsonResponse([
                'success' => false,
                'errors' => $this->formErrorsFormatter->toJsonResponseData($form->getErrors(true)),
                'data' => [
                    'renderedForm' => $renderedForm,
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var Order $order */
        $order = $form->getData();
        /** @var User $user */
        $user = $this->getUser();

        $order->setAuthor($user);
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => $this->translator->trans('order.create.success', [], 'apis'),
        ], Response::HTTP_CREATED);
    }

    #[Route('/details/{id}', name: 'api_order_details_get', methods: ['GET'])]
    public function detailsAction(?Order $order, int $id): Response
    {
        if (!$order) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->translator->trans('order.details.not_found', ['id' => $id], 'apis'),
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'success' => true,
            'data' => [
                'id' => $order->getId(),
                'renderedDetails' => $this->twig->render('orders/_details.html.twig', [
                    'order' => $order,
                    'logs' => [],
                ]),
            ],
        ]);
    }
}
