<?php

namespace App\Service\Invoices;

use App\Entity\Client;
use App\Entity\Company;
use App\Repository\CompanyRepository;
use Exception;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FakturowniaProvider
{
    private Company $company;

    public function __construct(
        private string $hostname,
        private string $token,
        private HttpClientInterface $httpClient,
        CompanyRepository $companyRepository
    ) {
        $this->company = $companyRepository->get();
        if (strlen($this->hostname) < 1) {
            $this->hostname = "test";
        }
    }

    /**
     * @param array $orders
     * @param Client $client
     * @return string
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function createInvoice(array $orders, Client $client): string
    {
        $payload = $this->getPayload($orders, $client);
        $body = [
            "api_token" => $this->token,
            "invoice" => $payload
        ];

        $url = 'https://' . $this->hostname . '.fakturownia.pl/invoices.json';

        $response = $this->httpClient->request(
            'POST',
            $url,
            [
                'body' => json_encode($body),
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        try {
            $response = $response->getContent();
        } catch (ExceptionInterface $e) {
            throw new Exception($response->getContent(false));
        }

        $result = json_decode($response, true);

        if (!isset($result['id'])) {
            $text = 'Bład serwisu Fakturownia.pl';
            if (isset($result['message'])) {
                $text .= ': ' . json_encode($result['message'], JSON_UNESCAPED_UNICODE);
            }
            throw new Exception($text);
        }

        return "https://" . $this->hostname . ".fakturownia.pl/invoices/";
    }

    private function getPayload($orders, $client): array
    {
        $positions = [];
        foreach ($orders as $order) {
            $positions[] = [
                'name' => $order->getTopic(),
                'quantity' => $order->getPages(),
                'total_price_gross' => $order->getBrutto(),
                'tax' => 23,
                'price_net' => $order->getPrice(),
            ];
        }

        return [
            'kind' => 'vat',
            'number' => null,
            'sell_date' => $this->company->getIssueDate()->format('Y-m-d'),
            'issue_date' => $this->company->getIssueDate()->format('Y-m-d'),
            'payment_to' => $this->company->getPaymentTo()->format('Y-m-d'),
            'seller_name' => $this->company->getName(),
            'seller_tax_no' => $this->company->getNip(),
            'seller_post_code' => $this->company->getPostCode(),
            'seller_city' => $this->company->getCity(),
            'seller_street' => $this->company->getAddress(),
            'seller_country' => 'PL',
            'seller_bank_account' => $this->company->getBankAccount(),
            'buyer_name' => $client->getName(),
            'buyer_tax_no' => $client->getNip(),
            'buyer_post_code' => $client->getPostCode(),
            'buyer_city' => $client->getCity(),
            'buyer_street' => $client->getStreet(),
            'buyer_country' => $client->getCountry(),
            'positions' => $positions,
        ];
    }
}
