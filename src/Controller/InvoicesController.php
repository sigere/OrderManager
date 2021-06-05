<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Company;
use App\Entity\Log;
use App\Entity\Order;
use App\Form\InvoiceMonthFormType;
use App\Form\InvoiceSummaryForm;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function curl_exec;
use function json_encode;

class InvoicesController extends AbstractController
{
    private $entityManager;
    private $request;
    private $company;

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
        $company = $this->entityManager->getRepository(Company::class)->findAll()[0];
        $clients = $this->loadClients($company->getInvoiceMonth());
        $form = $this->createForm(InvoiceSummaryForm::class, $company);
        $month = $company->getInvoiceMonth();
        $monthForm = $this->createForm(InvoiceMonthFormType::class, [
            "month" => $month ? intval($month->format('n')) : null,
            "year" => $month ? intval($month->format('Y')) : null,
        ]);

        return $this->render('invoices/index.html.twig', [
            'clients' => $clients,
            'company' => $company,
            'summaryForm' => $form->createView(),
            'monthForm' => $monthForm->createView(),
        ]);
    }

    private function loadClients(?DateTime $month): array
    {
        $repo = $this->entityManager->getRepository(Client::class);
        $clients = $repo->createQueryBuilder("c")
            ->andWhere("c.deletedAt is null")
            ->orderBy("c.alias", "ASC")
            ->getQuery()
            ->getResult();

        $repo = $this->entityManager->getRepository(Order::class);
        $result = [];

        foreach ($clients as $client) {
            try {
                $count = $repo->createQueryBuilder("o")
                    ->select("count(o.id)")
                    ->andWhere("o.deletedAt is null")
                    ->andWhere("o.settledAt is null");

                if ($month) $count = $count
                    ->andWhere("month(o.deadline) = :month")
                    ->andWhere("year(o.deadline) = :year")
                    ->setParameter("month", $month->format("n"))
                    ->setParameter("year", $month->format("Y"));

                $count = $count
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
        $company = $this->entityManager->getRepository(Company::class)->findAll()[0];
        $date = $company->getInvoiceMonth();
        $repo = $this->entityManager->getRepository(Order::class);
        $orders = $repo->createQueryBuilder("o")
            ->andWhere("o.deletedAt is null")
            ->andWhere("o.settledAt is null")
            ->andWhere("o.client = :client")
            ->setParameter("client", $client);

        if ($date) $orders = $orders
            ->andWhere("month(o.deadline) = :month")
            ->andWhere("year(o.deadline) = :year")
            ->setParameter("month", $date->format("n"))
            ->setParameter("year", $date->format("Y"));

        $orders = $orders
            ->orderBy("o.deadline", "ASC")
            ->getQuery()
            ->getResult();

        $nettoSum = 0.0;
        $validCount = 0;
        foreach ($orders as $order)
            if(count($order->getInvoiceWarnings()) == 0){
                $nettoSum += $order->getNetto();
                $validCount++;
            }

        return $this->render("invoices/orders_table.twig", [
            "orders" => $orders,
            "nettoSum" => $nettoSum,
            "validCount" =>$validCount,
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
        $company = $this->entityManager->getRepository(Company::class)->findAll()[0];
        $fakturowniaFirm = $this->getParameter("app.fakturownia_firm");
        $form = $this->createForm(InvoiceSummaryForm::class);
        $form->handleRequest($this->request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return new Response("<div class='alert alert-danger'>Niepoprawne dane</div>", 406);
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

        $payload = $this->getPayload(
            $orders,
            $this->request->get("client")
        );

        $url = 'https://' . $fakturowniaFirm . '.fakturownia.pl/invoices.json';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $result = json_decode($result, true);
        curl_close($ch);
        if (!isset($result['id'])) {
            $text = "Bład serwisu Fakturownia.pl";
            if (isset($result['message'])) $text .= ": " . json_encode($result['message'], JSON_UNESCAPED_UNICODE);
            return new Response("<div class='alert alert-danger'>" . $text . "</div>", 500);
        }

        $this->settle($orders);
        return new Response(
            "<div class='alert alert-success'>Wystawiono fakturę i ustawiono zlecenia na rozliczone.<br/><a href='https://" . $fakturowniaFirm . ".fakturownia.pl/invoices/" . $result["id"] . "'>Podgląd</a></div>",
            200);
    }

    private function getPayload($orders, $clientId): string
    {
        $company = $this->entityManager->getRepository(Company::class)->findAll()[0];
        $client = $this->entityManager
            ->getRepository(Client::class)
            ->findOneBy(["id" => $clientId]
            );

        $positions = [];
        foreach ($orders as $order)
            $positions[] = [
                'name' => $order->getTopic(),
                'quantity' => $order->getPages(),
                'total_price_gross' => $order->getBrutto(),
                'tax' => 23,
                'price_net' => $order->getPrice(),
            ];

        $token = $this->getParameter('app.fakturownia_token');
        $payload = [
            'api_token' => $token,
            'invoice' => [
                "kind" => "vat",
                "number" => null,
                "sell_date" => $company->getIssueDate()->format('Y-m-d'),
                "issue_date" => $company->getIssueDate()->format('Y-m-d'),
                "payment_to" => $company->getPaymentTo()->format('Y-m-d'),
                "seller_name" => $company->getName(),
                "seller_tax_no" => $company->getNip(),
                "seller_post_code" => $company->getPostCode(),
                "seller_city" => $company->getCity(),
                "seller_street" => $company->getAddress(),
                "seller_country" => "PL",
                "seller_bank_account" => $company->getBankAccount(),
                "buyer_name" => $client->getName(),
                "buyer_tax_no" => $client->getNip(),
                "buyer_post_code" => $client->getPostCode(),
                "buyer_city" => $client->getCity(),
                "buyer_street" => $client->getStreet(),
                "buyer_country" => $client->getCountry(),
                "positions" => $positions,
            ],
        ];
        return json_encode($payload);
    }

    private function settle(array $orders): void
    {
        foreach ($orders as $order) {
            if (!get_class($order) == Order::class)
                continue;
            $order->setSettledAt(new DateTime());
            $this->entityManager->persist($order);
            $this->entityManager->persist(new Log($this->getUser(), "Rozliczono zlecenie.", $order));
        }
        $this->entityManager->flush();
    }

    /**
     * @Route("/invoices/api/reloadClients", name="invoices_api_reloadClients")
     * @return Response
     */
    public function reloadClients(): Response
    {
        $company = $this->entityManager->getRepository(Company::class)->findAll()[0];
        $form = $this->createForm(InvoiceMonthFormType::class);
        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $month = $form->getData()['month'];
            $year = $form->getData()['year'];
            try {
                $date = new DateTime($year . '-' . $month . '-01');
            } catch (Exception $e) {
                $date = null;
            }
            $company->setInvoiceMonth($date);
            $this->entityManager->persist($company);
            $this->entityManager->flush();

            $clients = $this->loadClients($date);

            return $this->render('invoices/clients_table.twig', [
                "clients" => $clients,
            ]);
        }

        return new Response("Błedne dane.", 406);
    }

}
