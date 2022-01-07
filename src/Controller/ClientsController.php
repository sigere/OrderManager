<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Log;
use App\Form\AddClientForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClientsController extends AbstractController
{
    private $entityManager;
    private $request;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $request)
    {
        $this->entityManager = $entityManager;
        $this->request = $request->getCurrentRequest();
    }

    /**
     * @Route("/clients", name="clients")
     */
    public function index(): Response
    {
        return $this->render('clients/index.html.twig', [
            'clients' => $this->loadClientsTable(),
        ]);
    }

    private function loadClientsTable(): array
    {
        $repository = $this->entityManager->getRepository(Client::class);
        $clients = $repository->createQueryBuilder('c');
        $clients = $clients
            ->andWhere('c.deletedAt is null')
            ->orderBy('c.alias', 'ASC')
            ->getQuery()
            ->getResult();

        return $clients;
    }

    /**
     * @Route("/clients/api/reloadTable", name="clients_api_reload_table")
     */
    public function reloadTable(): Response
    {
        return $this->render('clients/clients_table.twig', [
            'clients' => $this->loadClientsTable(),
        ]);
    }

    /**
     * @Route("/clients/api/updateClient/{id}", name="clients_api_updateClient")
     */
    public function updateClient(Client $client): Response
    {
        $form = $this->createForm(AddClientForm::class, $client);

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $client = $form->getData();
            $this->entityManager->persist($client);
            $this->entityManager->persist(new Log($this->getUser(), 'Zaktualizowano klienta', $client));
            $this->entityManager->flush();

            return new Response('Zaktualizowano klienta.', 202, ['orderId' => $client->getId()]);
        }

        return $this->render('clients/addClient.html.twig', [
            'addClientForm' => $form->createView(),
            'update' => true
        ]);
    }

    /**
     * @Route("/clients/api/addClient", name="clients_api_addClient")
     */
    public function addClient(): Response
    {
        $form = $this->createForm(AddClientForm::class);
        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $client = $form->getData();
            $this->entityManager->persist($client);
            $this->entityManager->persist(new Log($this->getUser(), 'Dodano klienta klienta', $client));
            $this->entityManager->flush();

            return new Response('Dodano klienta', 201);
        }

        return $this->render('clients/addClient.html.twig', [
            'addClientForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/clients/api/details/{id}", name="clients_api_details")
     */
    public function details(Client $client): Response
    {
        $logs = $this->entityManager->getRepository(Log::class)->findBy(['client' => $client], ['createdAt' => 'DESC'], 100);

        return $this->render('clients/details.twig', [
            'client' => $client,
            'logs' => $logs,
        ]);
    }
}
