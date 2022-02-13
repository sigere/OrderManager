<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Log;
use App\Entity\Order;
use App\Form\AddOrderForm;
use App\Form\IndexFiltersForm;
use App\Repository\LogRepository;
use App\Repository\OrderRepository;
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
    private mixed $company;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderRepository $orderRepository,
        private LogRepository $logRepository,
        private IndexPreferences $preferences,
        RequestStack $request
    ) {
        $this->request = $request->getCurrentRequest();
        $this->company = $entityManager->getRepository(Company::class)->findAll()[0];
    }

    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        $orders = $this->orderRepository->getByIndexPreferences($this->preferences);
        $form = $this->createForm(IndexFiltersForm::class);
        $rep = $this->company->getRep();

        return $this->render('index/index.html.twig', [
            'orders' => $orders,
            'filtersForm' => $form->createView(),
            'preferences' => $this->preferences,
            'rep' => $rep,
        ]);
    }

    /**
     * @Route("/index/api/filters", name="index_api_filters")
     */
    public function indexApiFilters(): Response
    {
        $form = $this->createForm(IndexFiltersForm::class);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->preferences->applyForm($form->getData());
            return new Response('Zastosowano filtry.', 200);
        }

        return new Response('Błędne dane.', 400);
    }

    /**
     * @Route("/index/api/reloadTable", name="index_api_reloadTable")
     */
    public function reloadTable(): Response
    {
        $orders = $this->orderRepository->getByIndexPreferences($this->preferences);

        return $this->render('index/orders_table.twig', [
            'orders' => $orders,
            'preferences' => $this->preferences
        ]);
    }

    /**
     * @Route("/index/api/details/{id}", name="index_api_details")
     */
    public function details(Order $order): Response
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
     * @Route("/index/api/updateState/{id}/{state}", name="index_updateState")
     * @param Order $order
     * @param $state
     * @return Response
     */
    public function updateState(Order $order, $state): Response
    {
        if ($order->getState() == $state) {
            return new Response('State not changed');
        }

        $currentState = $order->getState();
        if (!in_array($state, Order::STATES)) {
            return new Response('given state not found', 404);
        }

        $order->setState($state);
        $this->entityManager->persist(new Log(
            $this->getUser(),
            'Zmiana statusu: ' . $currentState . ' -> ' . $state . '.',
            $order
        ));
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return new Response('Zmieniono status', 200);
    }

    /**
     * @Route("/index/api/deleteOrder/{id}", name="index_api_deleteOrder")
     */
    public function deleteOrder(Order $order): Response
    {
        if ($order->getDeletedAt()) {
            return new Response('Zlecenie zostało już usunięte', 406);
        }

        $order->setDeletedAt(new Datetime());
        $this->entityManager->persist($order);
        $this->entityManager->persist(new Log($this->getUser(), 'Usunięto zlecenie', $order));
        $this->entityManager->flush();

        return new Response('Zlecenie usunięte', 200);
    }

    /**
     * @Route("/index/api/addOrder", name="index_api_addorder")
     */
    public function addOrder(Request $request): Response
    {
        $form = $this->createForm(AddOrderForm::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $order = $form->getData();
            $order->setAuthor($this->getUser());

            $this->entityManager->persist($order);
            $this->entityManager->persist(new Log($this->getUser(), 'Dodano zlecenie', $order));
            $this->entityManager->flush();

            return new Response('Dodano zlecenie', 201, ['orderId' => $order->getId()]);
        }

        return $this->render('index/addOrder.html.twig', [
            'addOrderForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/index/api/updateOrder/{id}", name="index_api_updateOrder")
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

            return new Response('Zaktualizowano zlecenie.', 202, ['orderId' => $order->getId()]);
        }

        return $this->render('index/addOrder.html.twig', [
            'addOrderForm' => $form->createView(),
            'update' => true
        ]);
    }

    /**
     * @Route("/index/api/settle/{id}", name="index_api_settle")
     */
    public function settle(Order $order): Response
    {
        if (count($order->getWarnings())) {
            return new Response('Zlecenie nie może zostać rozliczone', 406);
        }

        if ($order->getSettledAt()) {
            return new Response('Zlecenie zostało już rozliczone.', 406);
        }

        $order->setSettledAt(new Datetime());
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return new Response('Rozliczono zlecenie', 200);
    }

    /**
     * @Route("/index/api/setRep/{rep}", name="index_api_setRep")
     * @param $rep
     * @return Response
     */
    public function setRep($rep): Response
    {
        $this->company->setRep($rep);
        $this->entityManager->persist($this->company);
        $this->entityManager->flush();

        return new Response('Wprowadzono zmiany', 200);
    }
}
