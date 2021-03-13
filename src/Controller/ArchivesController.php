<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Order;
use App\Entity\Staff;
use App\Form\ArchivesFiltersForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ArchivesController extends AbstractController
{
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/archives", name="archives")
     * @param Request $request
     * @param UrlGeneratorInterface $urlGenerator
     * @return Response
     */
    public function index(Request $request, UrlGeneratorInterface $urlGenerator): Response
    {
        $orders = $this->loadOrdersTable();
        $form = $this->createForm(ArchivesFiltersForm::class);


        return $this->render('archives/index.html.twig', [
            "orders" => $orders,
            "filtersForm" => $form->createView(),
        ]);
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
     * @Route("/archives/api/details/{id}", name="archives_get_details")
     * @param Order $order
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function details(Order $order, EntityManagerInterface $em): Response
    {
        $logs = $em->getRepository(Log::class)->findBy(['order' => $order], ['createdAt' => 'DESC']);
        return $this->render('archives/details.twig', [
            'order' => $order,
            'logs' => $logs
        ]);
    }

    /**
     * @Route("/archives/api/filters", name="archives_api_filters")
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @return Response
     */
    public function indexApiFilters(EntityManagerInterface $entityManager, Request $request): Response
    {
        $form = $this->createForm(ArchivesFiltersForm::class);
        $form->handleRequest($request);
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $preferences = $user->getPreferences();
            $preferences['archives'] = $form->getData();
            $preferences['archives']['staff'] = $preferences['archives']['staff'] ? $preferences['archives']['staff']->getId() : null;
            $preferences['archives']['select-client'] = $preferences['archives']['select-client'] ? $preferences['archives']['select-client']->getId() : null;
            $user->setPreferences($preferences);
            $entityManager->persist($user);
            $entityManager->flush();
        }

        return new Response(' return reached ');
    }

    /**
     * @Route("/archives/api/restore/{id}", name="archives_api_restore")
     * @param Order $order
     * @return Response
     */
    public function restore(Order $order): Response
    {
        if(!$order->getDeletedAt())
            return new Response("Zlecenie nie jest usunięte", 406);
        $order->setDeletedAt(null);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        return new Response("Zlecenie zostało przywrócone.", 200);
    }

    private function loadOrdersTable(): array
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(Order::class);
        $user = $this->getUser();
        $preferences = $user->getPreferences();
        $staff = $entityManager->getRepository(Staff::class)->findOneBy(['id' => $preferences['archives']['staff']]);
        //doctrine nie zapisuje obiektów w user->preferences['archives']['select-client'],
        //więc mapuje na id przy zapisie i na obiekt przy odczycie


        if ($preferences['archives']['usuniete']) {
            $orders = $repository->createQueryBuilder('o')
                ->andWhere('o.deletedAt is not null or o.settledAt is not null');

        } else {
            $orders = $repository->createQueryBuilder('o')
                ->andWhere('o.settledAt is not null');
        }

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

        $orders = $orders
            ->setMaxResults(400)
            ->orderBy('o.deadline', 'ASC')
            ->getQuery()
            ->getResult();

        return $orders;
    }
}
