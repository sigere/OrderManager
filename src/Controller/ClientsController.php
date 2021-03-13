<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\AddClientForm;
use DateTime;
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
            ->setMaxResults(400)
            ->orderBy('c.alias', 'ASC')
            ->getQuery()
            ->getResult();
        return $clients;
    }

    /**
     * @Route("/clients/api/reloadTable", name="clients_reload_table")
     */
    public function reloadTable(): Response
    {
        return $this->render('clients/clients_table.twig', [
            'clients' => $this->loadClientsTable(),
        ]);
    }


    /**
     * @Route("/clients/api/deleteClient/{id}", name="clients_api_deleteClient")
     * @param Client $client
     * @return Response
     */
    public function deleteClient(Client $client): Response
    {
        if ($client->getDeletedAt())
            return new Response("Klient został już usunięty", 406);

        $client->setDeletedAt(new Datetime());
        $this->entityManager->persist($client);
        //TODO logs
        $this->entityManager->flush();
        return new Response("Klient usunięty", 200);
    }

    /**
     * @Route("/clients/api/addClient", name="clients_api_addClient")
     * @return Response
     */
    public function addOrder(): Response
    {
        $form = $this->createForm(AddClientForm::class);

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $client = $form->getData();
            $this->entityManager->persist($client);
            //TODO logs
            $this->entityManager->flush();
            return new Response("Dodano klienta", 201);
        }

        return $this->render('clients/addClient.html.twig', [
            'addClientForm' => $form->createView(),
        ]);
    }


    /**
     * @Route("/clients/api/details/{id}", name="clients_api_details")
     * @param Client $client
     * @return Response
     */
    public function details(Client $client): Response
    {
        return $this->render('clients/details.twig', [
            'client' => $client
        ]);
    }
}
