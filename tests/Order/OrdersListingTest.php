<?php

namespace App\Tests\Order;

use App\Entity\Client;
use App\Entity\Order;
use App\Entity\Staff;
use App\Entity\User;
use App\Preferences\Model\OrderPreferences;
use App\Tests\AppWebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class OrdersListingTest extends AppWebTestCase
{
    public function testUserSeesOrders(): void
    {
        $user = $this->getUser();
        $preferences = $user->getPreferences()->getOrdersPreferences();

        $orders = $this->entityManager
            ->getRepository(Order::class)
            ->getByOrderPreferences($preferences);

        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/orders');

        // There are 8 orders witch are not deleted and not settled in the fixtures
        $this->assertCount(8, $orders);
        $this->assertOrdersCountInHTML($crawler, 8);
    }

    /**
     * @dataProvider preferencesDataProvider
     */
    public function testPreferencesAreRespected(callable $setupPreferences, array $expectedText): void
    {
        $user = $this->getUser();
        $this->client->loginUser($user);

        $preferences = clone $user->getPreferences();

        $staff = $this->entityManager
            ->getRepository(Staff::class)
            ->findOneBy(['firstName' => 'John', 'lastName' => 'Doe']);
        $this->assertInstanceOf(Staff::class, $staff);

        $setupPreferences($preferences->getOrdersPreferences(), $this);

        $user->setPreferences($preferences);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/orders');
        // dump($crawler->filter('div.mid-col .col-content')->text());
        $this->assertResponseIsSuccessful();
        $this->assertOrdersCountInHTML($crawler, count($expectedText));

        foreach ($expectedText as $text) {
            $this->assertStringContainsString(
                $text,
                $crawler->filter('div.mid-col .col-content')->text()
            );
        }
    }

    /**
     * Based on data from fixtures and default preferences.
     */
    private function preferencesDataProvider(): array
    {
        return [
            'John Doe\'s' => [
                function (OrderPreferences $preferences, self $self) {
                    $preferences->setStaff($self->getStaff('John', 'Doe'));
                },
                ['Order#A', 'Order#C', 'Order#E', 'Order#G', 'Order#I'],
            ],
            'For client_b' => [
                function (OrderPreferences $preferences, self $self) {
                    $preferences->setClient($self->getAppClient('client_b'));
                },
                ['Order#B', 'Order#E', 'Order#H'],
            ],
            'Jane Smith\'s with deleted' => [
                function (OrderPreferences $preferences, self $self) {
                    $preferences->setStaff($self->getStaff('Jane', 'Smith'));
                    $preferences->setDeleted(true);
                },
                ['Order#B', 'Order#D', 'Order#F', 'Order#H', 'Order#J'],
            ],
        ];
    }

    private function assertOrdersCountInHTML(Crawler $crawler, int $expectedCount): void
    {
        $rowCountContent = $crawler->filter('div.mid-col .col-content .rows-count span')->text();
        $this->assertMatchesRegularExpression('/^\d+\/'.$expectedCount.'$/', $rowCountContent);
    }
}
