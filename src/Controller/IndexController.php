<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Log;
use App\Entity\Order;
use App\Form\AddOrderForm;
use App\Form\IndexFiltersForm;
use App\Repository\LogRepository;
use App\Repository\OrderRepository;
use App\Service\ResponseFormatter;
use App\Service\UserPreferences\IndexPreferences;
use Datetime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $rep = $this->company->getRep();

        return $this->render('index/index.html.twig', [
            'orders' => $orders,
            'details' => [
                'order' => $order,
                'logs' => $logs
            ],
            'filtersForm' => $form->createView(),
            'preferences' => $this->preferences,
            'rep' => $rep,
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
            'preferences' => $this->preferences
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

        return $this->render('index/details.twig', [
            'order' => $order,
            'logs' => $logs,
        ]);
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
        $form = $this->createForm(AddOrderForm::class, $order);

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

        $order->setDeletedAt(new Datetime());
        $this->entityManager->persist($order);
        $this->entityManager->persist(new Log($this->getUser(), 'Usunięto zlecenie', $order));
        $this->entityManager->flush();

        return new Response(
            $this->formatter->success('Zlecenie usunięte'),
            200
        );
    }
}
