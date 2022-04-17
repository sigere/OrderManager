<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Order;
use App\Form\OrderForm;
use App\Form\DeleteEntityFrom;
use App\Form\IndexFiltersForm;
use App\Repository\LogRepository;
use App\Repository\OrderRepository;
use App\Service\OptionsProviderFactory;
use App\Service\ResponseFormatter;
use App\Service\UserPreferences\IndexPreferences;
use Datetime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    private ?Request $request;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderRepository $orderRepository,
        private LogRepository $logRepository,
        private IndexPreferences $preferences,
        private ResponseFormatter $formatter,
        private OptionsProviderFactory $optionsProviderFactory,
        RequestStack $request
    ) {
        $this->request = $request->getCurrentRequest();
    }

    /**
     * @Route("/", methods={"GET"}, name="index")
     */
    public function index(Request $request): Response
    {
        $orders = $this->orderRepository->getByIndexPreferences($this->preferences, $rowsCount);
        $order = $this->orderRepository->findOneBy(['id' => $request->get('order')]);
        $logs = $this->logRepository->findBy(
            ['order' => $order],
            ['createdAt' => 'DESC'],
            100
        );
        $form = $this->createForm(IndexFiltersForm::class);
        $options = $order ? $this->optionsProviderFactory->getOptions($order) : [];

        return $this->render('index/index.html.twig', [
            'orders' => $orders,
            'details' => [
                'order' => $order,
                'logs' => $logs
            ],
            'filtersForm' => $form->createView(),
            'preferences' => $this->preferences,
            'options' => $options,
            'rowsFound' => $rowsCount,
            'rowsShown' => min($rowsCount, $this->orderRepository::LIMIT),
            'dataSourceUrl' => '/order'
        ]);
    }

    /**
     * @Route("/locate", methods={"POST"}, name="search_post")
     */
    public function search(Request $request): Response
    {
        $id = $request->get('id');
        if ($id) {
            $order = $this->orderRepository->findOneBy(['id' => $id]);
            if (!$order) {
                return $this->render('index/locate_form.html.twig', [
                    'entity' => 'order',
                    'dataUrl' => '/locate',
                    'errors' => ['Order not found.']
                ]);
            }

            return new Response(
                $this->formatter->success("Znaleziono zlecenie."),
                200,
                ['Set-Current-Subject' => 'order/' . $order->getId()]
            );
        }

        return $this->render('index/locate_form.html.twig', [
            'entity' => 'order',
            'dataUrl' => '/locate',
        ]);
    }

    /**
     * @Route("/index/filters", methods={"POST"}, name="index_filters")
     */
    public function filters(): Response
    {
        $form = $this->createForm(IndexFiltersForm::class);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->preferences->applyForm($form->getData());

            return new Response(
                $this->formatter->success('Zastosowano filtry.'),
                200
            );
        }

        return new Response(
            $this->formatter->error('Błędne dane.'),
            400
        );
    }

    /**
     * @Route("/order", methods={"GET"}, name="order_get_all")
     */
    public function getOrders(): JsonResponse
    {
        $orders = $this->orderRepository->getByIndexPreferences($this->preferences, $rowsCount);

        $result['table'] = $this->renderView('index/orders_table.html.twig', [
            'orders' => $orders,
            'preferences' => $this->preferences,
            'dataSourceUrl' => '/order'
        ]);

        $result['rowsCount'] = $this->renderView('index/rows_count.html.twig', [
            'rowsFound' => $rowsCount,
            'rowsShown' => min($rowsCount, $this->orderRepository::LIMIT),
        ]);

        return new JsonResponse($result);
    }

    /**
     * @Route("/order/{id}", methods={"GET"}, name="order_get")
     */
    public function getOrder(Order $order): Response
    {
        $logs = $this->logRepository->findBy(
            ['order' => $order],
            ['createdAt' => 'DESC'],
            100
        );

        $options = $this->optionsProviderFactory->getOptions($order);

        $result = [];
        $result['details'] = $this->renderView('index/details.html.twig', [
            'order' => $order,
            'logs' => $logs,
        ]);

        $result['burger'] = $this->renderView('burger.html.twig', [
            'options' => $options
        ]);

        return new JsonResponse($result);
    }

    /**
     * @Route("/order", methods={"POST"}, name="order_post")
     */
    public function create(Request $request): Response
    {
        $form = $this->createForm(OrderForm::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $order = $form->getData();
            $order->setAuthor($this->getUser());

            $this->entityManager->persist($order);
            $this->entityManager->persist(new Log($this->getUser(), 'Dodano zlecenie', $order));
            $this->entityManager->flush();

            return new Response(
                $this->formatter->success('Dodano zlecenie'),
                201,
                ['Set-Current-Subject' => 'order/' . $order->getId()]
            );
        }

        return $this->render('index/order_form.html.twig', [
            'orderForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/order/{id}", methods={"PUT"}, name="order_put")
     */
    public function update(Order $order): Response
    {
        $attr = array_merge(OrderForm::DEFAULT_OPTIONS['attr'] ?? [], [
            'data-url' => '/order/' . $order->getId(),
            'data-method' => 'PUT'
        ]);
        $options = array_merge(OrderForm::DEFAULT_OPTIONS, [
            'attr' => $attr,
            'method' => 'PUT'
        ]);

        $form = $this->createForm(OrderForm::class, $order, $options);

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $order = $form->getData();
            $this->entityManager->persist($order);
            $this->entityManager->persist(new Log($this->getUser(), 'Zaktualizowano zlecenie', $order));
            $this->entityManager->flush();

            return new Response(
                $this->formatter->success('Zaktualizowano zlecenie.'),
                202,
                ['Set-Current-Subject' => 'order/' . $order->getId()]
            );
        }

        return $this->render('index/order_form.html.twig', [
            'orderForm' => $form->createView(),
            'update' => true
        ]);
    }

    /**
     * @Route("/order/{id}", methods={"DELETE"}, name="order_delete")
     */
    public function delete(Order $order): Response
    {
        if ($order->getDeletedAt()) {
            return new Response(
                $this->formatter->notice('Zlecenie zostało już usunięte'),
                406
            );
        }

        $attr = array_merge(DeleteEntityFrom::DEFAULT_OPTIONS['attr'] ?? [], [
            'data-url' => '/order/' . $order->getId(),
        ]);
        $options = array_merge(DeleteEntityFrom::DEFAULT_OPTIONS, ['attr' => $attr]);
        $form = $this->createForm(DeleteEntityFrom::class, null, $options);

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $order->setDeletedAt(new Datetime());
            $this->entityManager->persist($order);
            $this->entityManager->persist(new Log($this->getUser(), 'Usunięto zlecenie', $order));
            $this->entityManager->flush();

            return new Response(
                $this->formatter->success('Zlecenie usunięte'),
                200,
                ['Set-Current-Subject' => 'order/' . $order->getId()]
            );
        }

        return $this->render('delete_entity_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/order/{id}/restore", methods={"POST"}, name="order_restore")
     */
    public function restore(Order $order): Response
    {
        if (!$order->getDeletedAt()) {
            return new Response(
                $this->formatter->notice('Zlecenie nie jest usunięte'),
                406
            );
        }

        $options = [
            'method' => 'POST',
            'attr' => [
                'method' => null,
                'data-method' => 'POST',
                'data-url' => '/order/' . $order->getId() . '/restore'
            ]
        ];
        $form = $this->createForm(DeleteEntityFrom::class, null, $options);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order->setDeletedAt(null);
            $this->entityManager->persist($order);
            $this->entityManager->persist(new Log($this->getUser(), 'Przywrócono zlecenie', $order));
            $this->entityManager->flush();

            return new Response(
                $this->formatter->success('Zlecenie przywrócone'),
                200,
                ['Set-Current-Subject' => 'order/' . $order->getId()]
            );
        }

        return $this->render('delete_entity_form.html.twig', [
            'form' => $form->createView(),
            'restore' => true
        ]);
    }
}
