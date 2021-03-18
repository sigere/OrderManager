<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Order;
use App\Entity\Staff;
use App\Form\ArchivesFiltersForm;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArchivesController extends AbstractController
{
    private $entityManager;
    private $request;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $request)
    {
        $this->entityManager = $entityManager;
        $this->request = $request->getCurrentRequest();
    }

    /**
     * @Route("/archives", name="archives")
     * @return Response
     */
    public function index(): Response
    {
        $orders = $this->loadOrdersTable();
        $form = $this->createForm(ArchivesFiltersForm::class);


        return $this->render('archives/index.html.twig', [
            "orders" => $orders,
            "filtersForm" => $form->createView(),
        ]);
    }

    private function loadOrdersTable(): array
    {
        $repository = $this->entityManager->getRepository(Order::class);
        $user = $this->getUser();
        $preferences = $user->getPreferences();
        $staff = $this->entityManager->getRepository(Staff::class)->findOneBy(['id' => $preferences['archives']['staff']]);
        //doctrine nie zapisuje obiektów w user->preferences['archives']['select-client'],
        //więc mapuje na id przy zapisie i na obiekt przy odczycie

        $orders = $repository->createQueryBuilder('o');

        if ($preferences['archives']['usuniete'])
            $orders->andWhere('o.deletedAt is not null or o.settledAt is not null');
        else
            $orders->andWhere('o.settledAt is not null');

        if ($preferences['archives']['staff']) {
            $orders = $orders
                ->andWhere('o.staff = :staff')
                ->setParameter('staff', $staff ? $staff : $this->getUser());
        }

        if ($preferences['archives']['select-client'])
            $orders = $orders
                ->andWhere('o.client = :client')
                ->setParameter('client', $repository->findOneBy(['id' => $preferences['archives']['select-client']]));
        //doctrine nie zapisuje obiektów w user->preferences['archives']['select-client'],
        //więc mapuje na id przy zapisie i na obiekt przy odczycie

        $dateType = $preferences['archives']['date-type'];
        if ($preferences['archives']['date-from']) {
            $dateFrom = new Datetime($preferences['archives']['date-from']['date']);
            $orders
                ->andWhere('o.' . $dateType . ' >= :dateFrom')
                ->setParameter('dateFrom', $dateFrom);
        }
        if ($preferences['archives']['date-to']) {
            $dateTo = new Datetime($preferences['archives']['date-to']['date']);
            $dateTo->setTime(23, 59);
            $orders
                ->andWhere('o.' . $dateType . ' <= :dateTo')
                ->setParameter('dateTo', $dateTo);
        }

        $orders = $orders
            ->setMaxResults(15)
            ->orderBy('o.deadline', 'ASC')
            ->getQuery()
            ->getResult();

        return $orders;
    }

    /**
     * @Route("/archives/api/reloadTable", name="archives_reload_table")
     */
    public function reloadTable(): Response
    {
        $orders = $this->loadOrdersTable();
        return $this->render('archives/orders_table.twig', [
            'orders' => $orders
        ]);
    }

    /**
     * @Route("/archives/api/details/{id}", name="archives_details")
     * @param Order $order
     * @return Response
     */
    public function details(Order $order): Response
    {
        $logs = $this->entityManager->getRepository(Log::class)->findBy(['order' => $order], ['createdAt' => 'DESC']);
        return $this->render('archives/details.twig', [
            'order' => $order,
            'logs' => $logs
        ]);
    }

    /**
     * @Route("/archives/api/filters", name="archives_api_filters")
     * @return Response
     */
    public function filters(): Response
    {
        $form = $this->createForm(ArchivesFiltersForm::class);
        $form->handleRequest($this->request);
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $preferences = $user->getPreferences();
            $preferences['archives'] = $form->getData();
            $preferences['archives']['staff'] = $preferences['archives']['staff'] ? $preferences['archives']['staff']->getId() : null;
            $preferences['archives']['select-client'] = $preferences['archives']['select-client'] ? $preferences['archives']['select-client']->getId() : null;
            $user->setPreferences($preferences);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return new Response('Zaktualizowano preferencje', 200);
    }

    /**
     * @Route("/archives/api/restoreOrder/{id}", name="archives_api_restoreOrder")
     * @param Order $order
     * @return Response
     */
    public function restore(Order $order): Response
    {
        if (!$order->getDeletedAt())
            return new Response("Zlecenie nie jest usunięte", 406);
        $order->setDeletedAt(null);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        return new Response("Zlecenie zostało przywrócone.", 200);
    }
}
