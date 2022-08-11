<?php

namespace App\Tests\Unit\Service\Invoices;

use App\Entity\Client;
use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Service\Invoices\FakturowniaProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FakturowniaProviderTest extends TestCase
{
    private Client $client;
    private Company $company;

    protected function setUp(): void
    {
        $this->client = $this->createConfiguredMock(Client::class, [
            'getName' => "lorem",
            'getNip' => "123456",
            'getPostCode' => "12-345",
            'getCity' => "New York",
            'getStreet' => "RedStreet",
            'getCountry' => "Afghanistan",
        ]);

        $this->company = $this->createConfiguredMock(Company::class, [
            'getName' => "ipsum",
            'getNip' => "98765",
            'getPostCode' => "32-345",
            'getCity' => "Old York",
            'getAddress' => "WhiteStreet",
            'getBankAccount' => "999901231123"
        ]);
    }

    public function test_payload_is_valid()
    {
        $client = $this->client;
        $company = $this->company;
        $positions = []; //todo
        $issueDate = new \DateTime("2000-01-01");
        $paymentTo = new \DateTime("2000-02-02");

        $expectedBody = [
            "api_token" => "",
            "invoice" => [
                'kind' => 'vat',
                'number' => null,
                'sell_date' => $issueDate->format('Y-m-d'),
                'issue_date' => $issueDate->format('Y-m-d'),
                'payment_to' => $paymentTo->format('Y-m-d'),
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
                'positions' => $positions,]
        ];

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->exactly(1))->method('request')->with(
            "POST",
            'https://test.fakturownia.pl/invoices.json',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode($expectedBody)
            ]
        );

        $this->expectException(\Exception::class);
        $provider = new FakturowniaProvider(
            "",
            "",
            $httpClient,
            $this->createConfiguredMock(CompanyRepository::class, [
                'get' => $company
            ])
        );
        $provider->createInvoice([], $client, $issueDate, $paymentTo);
    }
}
