<?php

namespace App\Tests\Order;

use App\Entity\Order;
use App\Entity\Staff;
use App\Preferences\Model\DateType;
use App\Preferences\Model\OrderPreferences;
use App\Tests\AppWebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class ListingTest extends AppWebTestCase
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
        $html = $crawler->filter('div.mid-col .col-content')->text();
        preg_match_all('/\b\w*#\w*\b/', $html, $matches);

        $this->assertResponseIsSuccessful();
        $this->assertOrdersCountInHTML($crawler, count($expectedText));

        $this->assertEqualsCanonicalizing($expectedText, $matches[0]);
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
                    $preferences
                        ->setStaff($self->getStaff('Jane', 'Smith'))
                        ->setDeleted(true);
                },
                ['Order#B', 'Order#D', 'Order#F', 'Order#H', 'Order#J'],
            ],
            'With deadline between 11.01.2024 and 01.02.2024' => [
                function (OrderPreferences $preferences, self $self) {
                    $preferences
                        ->setDateType(DateType::Deadline)
                        ->setDateFrom(new \DateTime('2024-01-11'))
                        ->setDateTo(new \DateTime('2024-02-01'));
                },
                ['Order#C', 'Order#E'],
            ],
            'With adoption between 02.01.2024 and 11.01.2024' => [
                function (OrderPreferences $preferences, self $self) {
                    $preferences
                        ->setDateType(DateType::Adoption)
                        ->setDateFrom(new \DateTime('2024-01-02'))
                        ->setDateTo(new \DateTime('2024-01-11'));
                },
                ['Order#I', 'Order#J'],
            ],
        ];
    }

    private function assertOrdersCountInHTML(Crawler $crawler, int $expectedCount): void
    {
        $rowCountContent = $crawler->filter('div.mid-col .col-content .rows-count span')->text();
        $this->assertMatchesRegularExpression('/^\d+\/'.$expectedCount.'$/', $rowCountContent);
    }
}
