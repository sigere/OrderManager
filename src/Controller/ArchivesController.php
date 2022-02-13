<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Order;
use App\Form\ArchivesFiltersForm;
use App\Repository\OrderRepository;
use App\Service\UserPreferences\ArchivesPreferences;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArchivesController extends AbstractController
{
    private ?Request $request;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderRepository $orderRepository,
        private ArchivesPreferences $preferences,
        RequestStack $request
    ) {
        $this->request = $request->getCurrentRequest();
    }

    /**
     * @Route("/archives", name="archives")
     */
    public function index(): Response
    {
        $orders = $this->orderRepository->getByArchivesPreferences($this->preferences);
        $form = $this->createForm(ArchivesFiltersForm::class);

        return $this->render('archives/index.html.twig', [
            'orders' => $orders,
            'filtersForm' => $form->createView(),
            'preferences' => $this->preferences
        ]);
    }

    /**
     * @Route("/archives/api/reloadTable", name="archives_reload_table")
     */
    public function reloadTable(): Response
    {
        $orders = $this->orderRepository->getByArchivesPreferences($this->preferences);

        return $this->render('archives/orders_table.twig', [
            'orders' => $orders,
        ]);
    }

    /**
     * @Route("/archives/api/details/{id}", name="archives_details")
     */
    public function details(Order $order): Response
    {
        $logs = $this->entityManager->getRepository(Log::class)->findBy(['order' => $order], ['createdAt' => 'DESC']);

        return $this->render('archives/details.twig', [
            'order' => $order,
            'logs' => $logs,
        ]);
    }

    /**
     * @Route("/archives/api/filters", name="archives_api_filters")
     */
    public function filters(): Response
    {
        $form = $this->createForm(ArchivesFiltersForm::class);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->preferences->applyForm($form->getData());
            return new Response('Zastosowano filtry.', 200);
        }

        return new Response('Błędne dane.', 400);
    }

    /**
     * @Route("/archives/api/restoreOrder/{id}", name="archives_api_restoreOrder")
     */
    public function restore(Order $order): Response
    {
        if (!$order->getDeletedAt()) {
            return new Response(
                "<div class='alert alert-danger'>Zlecenie nie jest usunięte</div>",
                406
            );
        }
        $order->setDeletedAt(null);
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return new Response(
            "<div class='alert alert-success'>Zlecenie zostało przywrócone.</div>",
            200
        );
    }
}
