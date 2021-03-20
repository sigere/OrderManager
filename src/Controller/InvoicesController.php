<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Company;
use App\Entity\Log;
use App\Entity\Order;
use App\Form\InvoiceSummaryForm;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvoicesController extends AbstractController
{
    private $entityManager;
    private $request;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @Route("/invoices", name="invoices")
     */
    public function index(): Response
    {
        $clients = $this->loadClients();
        $company = $this->entityManager->getRepository(Company::class)->findAll()[0];
        $form = $this->createForm(InvoiceSummaryForm::class, $company);

        return $this->render('invoices/index.html.twig', [
            'clients' => $clients,
            'company' => $company,
            'summaryForm' => $form->createView(),
        ]);
    }

    private function loadClients(): array
    {
        $repo = $this->entityManager->getRepository(Client::class);
        $clients = $repo->createQueryBuilder("c")
            ->andWhere("c.deletedAt is null")
            ->orderBy("c.alias", "ASC")
            ->getQuery()
            ->getResult();;

        $repo = $this->entityManager->getRepository(Order::class);
        $result = [];
        foreach ($clients as $client) {
            try {
                $count = $repo->createQueryBuilder("o")
                    ->select("count(o.id)")
                    ->andWhere("o.deletedAt is null")
                    ->andWhere("o.settledAt is null")
                    ->andWhere("o.client = :client")
                    ->setParameter("client", $client)
                    ->getQuery()
                    ->getSingleScalarResult();
            } catch (NoResultException | NonUniqueResultException $e) {
                $count = 0;
            }
            if ($count) {
                $result[] = [$client, $count];
            }
        }

        return $result;
    }

    /**
     * @Route("/invoices/api/reloadOrders/{id}", name="invoices_api_reloadOrders")
     * @param Client $client
     * @return Response
     */
    public function reloadOrders(Client $client): Response
    {
        $repo = $this->entityManager->getRepository(Order::class);
        $orders = $repo->createQueryBuilder("o")
            ->andWhere("o.deletedAt is null")
            ->andWhere("o.settledAt is null")
            ->andWhere("o.client = :client")
            ->setParameter("client", $client)
            ->orderBy("o.deadline", "ASC")
            ->getQuery()
            ->getResult();

        return $this->render("invoices/orders_table.twig", [
            "orders" => $orders,
        ]);
    }

    /**
     * @Route("/invoices/api/reloadClient/{id}", name="invoices_api_reloadClient")
     * @param Client $client
     * @return Response
     */
    public function reloadClient(Client $client): Response
    {
        return $this->render('invoices/buyerDetails.twig', [
            'client' => $client,
        ]);
    }

    /**
     * @Route("/invoices/api/execute", name="invoices_api_execute")
     * @return Response
     */
    public function executeInvoice(): Response
    {
        dump($_POST);
        $company = $this->entityManager->getRepository(Company::class)->findAll()[0];
        $form = $this->createForm(InvoiceSummaryForm::class);
        $form->handleRequest($this->request);
        if(!$form->isSubmitted() || !$form->isValid()){
            return new Response("Niepoprawne dane", 406);
        }
        $company->setPaymentTo($form->getData()["paymentTo"]);
        $company->setIssueDate($form->getData()["issueDate"]);
        $this->entityManager->persist($company);
        $this->entityManager->flush();
        $ids = $this->request->get("orders");
        $repo = $this->entityManager->getRepository(Order::class);
        $orders = [];
        foreach ($ids as $id)
            $orders[] = $repo->findOneBy(["id" => $id]);

        $this->settle($orders);


        return new Response("Rozliczono",200);
    }

    private function settle(array $orders): void
    {
        foreach ($orders as $order)
        {
            if(!get_class($order) == Order::class)
                continue;
            $order->setSettledAt(new \DateTime());
            $this->entityManager->persist($order);
            $this->entityManager->persist(new Log($this->getUser(),"Rozliczono zlecenie.",$order));
        }
        $this->entityManager->flush();
    }

}
