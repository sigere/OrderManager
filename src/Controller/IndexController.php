<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Log;
use App\Entity\Order;
use App\Form\AddOrderForm;
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
    private Company $company;

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
        $this->company = $entityManager->getRepository(Company::class)->findAll()[0];
    }

    /**
     * @Route("/", methods={"GET"}, name="index")
     */
    public function index(Request $request): Response
    {
        $orders = $this->orderRepository->getByIndexPreferences($this->preferences);
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
            'dataSourceUrl' => '/order'
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
    public function getOrders(): Response
    {
        $orders = $this->orderRepository->getByIndexPreferences($this->preferences);

        return $this->render('index/orders_table.twig', [
            'orders' => $orders,
            'preferences' => $this->preferences,
            'dataSourceUrl' => '/order'
        ]);
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
        $result['details'] = $this->renderView('index/details.twig', [
            'order' => $order,
            'logs' => $logs,
        ]);

        $result['burger'] = $this->renderView('burger.html.twig', [
            'options' => $options
        ]);

        return new JsonResponse(json_encode($result));
    }

    /**
     * @Route("/order", methods={"POST"}, name="order_post")
     */
    public function create(Request $request): Response
    {
        $form = $this->createForm(AddOrderForm::class);

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
                ['orderId' => $order->getId()] //todo unnecessary data
            );
        }

        return $this->render('index/addOrder.html.twig', [
            'addOrderForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/order/{id}", methods={"PUT"}, name="order_put")
     */
    public function updateOrder(Order $order): Response
    {
        $attr = array_merge(AddOrderForm::DEFAULT_OPTIONS['attr'] ?? [], [
            'data-url' => '/order/' . $order->getId(),
            'data-method' => 'PUT'
        ]);
        $options = array_merge(AddOrderForm::DEFAULT_OPTIONS, [
            'attr' => $attr,
            'method' => 'PUT'
        ]);

        $form = $this->createForm(AddOrderForm::class, $order, $options);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order = $form->getData();
            $this->entityManager->persist($order);
            $this->entityManager->persist(new Log($this->getUser(), 'Zaktualizowano zlecenie', $order));
            $this->entityManager->flush();

            return new Response(
                $this->formatter->success('Zaktualizowano zlecenie.'),
                202,
                ['orderId' => $order->getId()] //todo unnecessary data
            );
        }

        return $this->render('index/addOrder.html.twig', [
            'addOrderForm' => $form->createView(),
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
                200
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
                200
            );
        }

        return $this->render('delete_entity_form.html.twig', [
            'form' => $form->createView(),
            'restore' => true
        ]);
    }
}
