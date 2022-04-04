<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Company;
use App\Entity\Invoice;
use App\Entity\Log;
use App\Entity\Order;
use App\Form\InvoiceMonthForm;
use App\Repository\ClientRepository;
use App\Repository\CompanyRepository;
use App\Repository\OrderRepository;
use App\Service\Invoices\FakturowniaProvider;
use App\Service\ResponseFormatter;
use App\Service\UserPreferences\InvoicesPreferences;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * @Route("/invoices")
 */
class InvoicesController extends AbstractController
{
    private Company $company;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderRepository $orderRepository,
        private InvoicesPreferences $preferences,
        private ClientRepository $clientRepository,
        private ResponseFormatter $formatter,
        private FakturowniaProvider $provider,
        CompanyRepository $companyRepository,
    ) {
        $this->company = $companyRepository->get();
    }

    /**
     * @Route("/", name="invoices")
     */
    public function index(): Response
    {
        $clients = $this->clientRepository->getForInvoices(
            $this->preferences->getYear() ?? (int) (new \DateTime())->format('Y'),
            $this->preferences->getMonth()
        );

        $month = $this->company->getInvoiceMonth();
        $monthForm = $this->createForm(InvoiceMonthForm::class, [
            'month' => $month ? intval($month->format('n')) : null,
            'year' => $month ? intval($month->format('Y')) : null,
        ]);

        return $this->render('invoices/index.html.twig', [
            'clients' => $clients,
            'company' => $this->company,
            'monthForm' => $monthForm->createView(),
            'preferences' => $this->preferences
        ]);
    }

    /**
     * @Route("/client", methods={"GET"}, name="invoices_client_get_all")
     */
    public function getAll(Request $request): Response
    {
        $year = $request->get('year');
        $month = $request->get('month');

        if (!$year) {
            return new Response($this->formatter->error("Year not specified."), 400);
        }

        $clients = $this->clientRepository->getForInvoices($year, $month);
        $this->preferences
            ->setMonth($month)
            ->setYear($year)
            ->save();

        return $this->render('invoices/clients_table.html.twig', [
            'clients' => $clients,
        ]);
    }

    /**
     * @Route("/client/{id}", methods={"GET"}, name="invoices_client_get")
     */
    public function getClient(Client $client): Response
    {
        $orders = $this->orderRepository->getForInvoicingByClient(
            $client,
            $this->preferences->getYear(),
            $this->preferences->getMonth()
        );

        $nettoSum = 0.0;
        $validCount = 0;
        foreach ($orders as $order) {
            if (count($order->getInvoiceWarnings()) == 0) {
                $nettoSum += $order->getNetto();
                $validCount++;
            }
        }

        $result['orders'] = $this->renderView('invoices/orders_table.html.twig', [
            'orders' => $orders,
            'nettoSum' => $nettoSum,
            'validCount' => $validCount,
        ]);

        $result['client'] = $this->renderView('invoices/buyer_details.html.twig', [
            'client' => $client
        ]);

        return new JsonResponse($result);
    }

    /**
     * @Route("/invoice", methods={"POST"}, name="invoices_invoice_post")
     */
    public function createInvoice(Request $request): Response
    {
        $client = $this->clientRepository->findOneBy(['id' => $request->get('client')]);
        $issueDate = $request->get('issue_date');
        $paymentDate = $request->get('payment_date');
        $ids = $request->get('orders');

        if (!$client || !$issueDate || !$paymentDate || !$ids) {
            return new Response($this->formatter->error("Niepoprawne dane."), 400);
        }

        $orders = $this->orderRepository->createQueryBuilder('o')
            ->andWhere('o.id in (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        try {
            $link = $this->provider->createInvoice($orders, $client);
        } catch (ExceptionInterface|\Exception $e) {
            return new Response($this->formatter->error(
                "Bład serwisu: " . $e->getMessage()
            ), 500);
        }

        $this->settle($orders);
        $this->logInvoice($orders);

        $link = "<a href='" . $link . "'>Podgląd</a>";

        return new Response(
            $this->formatter->success("Wystawiono fakturę i ustawiono zlecenia na rozliczone" . $link),
            200
        );
    }

    /**
     * @Route("/settle", methods={"PUT"}, name="invoices_settle_put")
     */
    public function settle(Request $request): Response
    {
        $ids = $request->get('orders');
        if (!$ids) {
            return new Response(
                $this->formatter->error("Nie wybrano żadnych zleceń"),
                406
            );
        }

        $orders = $this->orderRepository->createQueryBuilder('o')
            ->andWhere('o.id in (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        $this->settleOrders($orders);
        $this->logInvoice($orders);

        return new Response(
            $this->formatter->success("Ustawiono zlecenia na rozliczone"),
            200
        );
    }

    private function settleOrders(array $orders): void
    {
        foreach ($orders as $order) {
            if (!($order instanceof Order)) {
                continue;
            }
            $order->setSettledAt(new DateTime());
            $this->entityManager->persist($order);
            $this->entityManager->persist(new Log($this->getUser(), 'Rozliczono zlecenie.', $order));
        }

        $this->entityManager->flush();
    }
  
    private function logInvoice(Array $orders): void
    {
        $invoice = new Invoice($this->getUser());
        foreach ($orders as $order) {
            $invoice->addOrder($order);
        }
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();
    }
}
