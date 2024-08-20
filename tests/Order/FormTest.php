<?php

declare(strict_types=1);

namespace App\Tests\Order;

use App\Entity\Order;
use App\Entity\Staff;
use App\Tests\AppWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;

class FormTest extends AppWebTestCase
{
    public function testSomething(): void
    {
        $entityManager = $this->getContainer()->get(EntityManagerInterface::class);
        $uow = $entityManager->getUnitOfWork();

        /** @var Staff $staff */
        $staff = $entityManager->getRepository(Staff::class)->findOneBy(['firstName' => 'John']);

        $staff->setLastName('Dupa');

        $entityManager->persist($staff);
        $uow->computeChangeSets();
        $uow->computeChangeSet();
        dump($uow->getEntityChangeSet($staff));
        $entityManager->flush();
    }

    public function testOrderIsCreated(): void
    {
        $user = $this->getUser();
        $this->client->loginUser($user);

        $this->client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $crawler = $this->client->request('POST', '/api/order');

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertInstanceOf(JsonResponse::class, $response);

        $array = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('data', $array);
        $this->assertArrayHasKey('renderedForm', $array['data']);

        // Create crawler based on html from JSON response
        $crawler = new Crawler(null, $crawler->getUri());
        $crawler->addContent($array['data']['renderedForm']);

        $buttonNode = $crawler->selectButton('OK');
        $this->assertEquals(1, $buttonNode->count());
        $form = $buttonNode->form([
            'order_form' => [
                'topic' => 'Order ##A',
                'pages' => 10,
                'price' => 100,
            ],
        ], 'POST');

        $this->client->submit($form);
        $this->assertResponseIsSuccessful();

        $order = $this->entityManager->getRepository(Order::class)->findOneBy([
            'topic' => 'Order ##A',
        ]);

        $this->assertNotNull($order);
    }

    /**
     * @dataProvider orderValidationDataProvider
     */
    public function testOrderValidation(array|callable $formData, callable $makeAssertions): void
    {
        $user = $this->getUser();
        $this->client->loginUser($user);
        $this->client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $crawler = $this->client->request('POST', '/api/order');

        $response = $this->client->getResponse();
        $array = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

        // Create crawler based on html from JSON response
        $crawler = new Crawler(null, $crawler->getUri());
        $crawler->addContent($array['data']['renderedForm']);

        $this->assertResponseIsSuccessful();
        $this->assertEquals(1, $crawler->filter('form[name="order_form"]')->count());

        $form = $crawler->selectButton('OK')->form();

        if (is_callable($formData)) {
            $formData($form);
        } else {
            $form->setValues(['order_form' => $formData]);
        }

        $this->client->submit($form);

        $makeAssertions($this->client);
    }

    public function orderValidationDataProvider(): array
    {
        return [
            'Empty (default) data' => [
                [],
                function (KernelBrowser $client) {
                    $this->assertResponseStatusCodeSame(400);
                    $this->assertStringContainsString(
                        'Topic cannot be blank', $client->getResponse()->getContent()
                    );
                },
            ],
        ];
    }
}
