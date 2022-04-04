<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Log;
use App\Form\AddClientForm;
use App\Repository\ClientRepository;
use App\Repository\LogRepository;
use App\Service\OptionsProviderFactory;
use App\Service\ResponseFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/clients")
 */
class ClientsController extends AbstractController
{
    private ?Request $request;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ClientRepository $clientRepository,
        private LogRepository $logRepository,
        private OptionsProviderFactory $optionsProviderFactory,
        private ResponseFormatter $formatter,
        RequestStack $request
    ) {
        $this->request = $request->getCurrentRequest();
    }

    /**
     * @Route("/", methods={"GET"}, name="clients")
     */
    public function index(): Response
    {
        $clients = $this->clientRepository->getForDefaultView();
        $client = $this->clientRepository->findOneBy(['id' => $this->request->get('client')]);
        $logs = $this->logRepository->findBy(
            ['client' => $client],
            ['createdAt' => 'DESC'],
            100
        );

        $options = $client ? $this->optionsProviderFactory->getOptions($client) : [];

        return $this->render('clients/index.html.twig', [
            'clients' => $clients,
            'details' => [
                'client' => $client,
                'logs' => $logs
            ],
            'options' => $options,
            'dataSourceUrl' => '/clients/client',
        ]);
    }

    /**
     * @Route("/client", methods={"GET"}, name="clients_client_get_all")
     */
    public function getAll(): Response
    {
        $clients = $this->clientRepository->getForDefaultView();

        return $this->render('clients/clients_table.html.twig', [
            'clients' => $clients,
            'dataSourceUlr' => '/clients/client'
        ]);
    }

    /**
     * @Route("/client/{id}", methods={"GET"}, name="clients_client_get")
     */
    public function getClient(Client $client): Response
    {
        $logs = $this->logRepository->findBy(
            ['client' => $client],
            ['createdAt' => 'DESC'],
            100
        );

        $options = $this->optionsProviderFactory->getOptions($client);

        $result = [];
        $result['details'] = $this->renderView('clients/details.html.twig', [
            'client' => $client,
            'logs' => $logs,
        ]);

        $result['burger'] = $this->renderView('burger.html.twig', [
            'options' => $options
        ]);

        return new JsonResponse($result);
    }

    /**
     * @Route("/client", methods={"POST"}, name="clients_client_post")
     */
    public function create(): Response
    {
        $form = $this->createForm(AddClientForm::class);

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $client = $form->getData();
            $client->setAuthor($this->getUser());

            $this->entityManager->persist($client);
            $this->entityManager->persist(new Log($this->getUser(), 'Dodano klienta', $client));
            $this->entityManager->flush();

            return new Response(
                $this->formatter->success('Dodano klienta'),
                201
            );
        }

        return $this->render('clients/client_form.html.twig', [
            'addClientForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/client/{id}", methods={"PUT"}, name="clients_client_put")
     */
    public function update(Client $client): Response
    {
        $attr = array_merge(AddClientForm::DEFAULT_OPTIONS['attr'] ?? [], [
            'data-url' => '/order/' . $client->getId(),
            'data-method' => 'PUT'
        ]);
        $options = array_merge(AddClientForm::DEFAULT_OPTIONS, [
            'attr' => $attr,
            'method' => 'PUT'
        ]);

        $form = $this->createForm(AddClientForm::class, $client, $options);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client = $form->getData();
            $this->entityManager->persist($client);
            $this->entityManager->persist(new Log($this->getUser(), 'Zaktualizowano klienta', $client));
            $this->entityManager->flush();

            return new Response(
                $this->formatter->success('Zaktualizowano klienta.'),
                202
            );
        }

        return $this->render('clients/client_form.html.twig', [
            'addClientForm' => $form->createView(),
            'update' => true
        ]);
    }
}
