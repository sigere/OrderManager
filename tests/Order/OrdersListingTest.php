<?php

namespace App\Tests\Order;

use App\Entity\Order;
use App\Entity\User;
use App\Preferences\Model\DateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrdersListingTest extends WebTestCase
{
    public function testUserSeesOrders(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/orders');

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $user = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'tester1']);

        $preferences = $user->getPreferences()->getOrdersPreferences();
        $this->assertEquals(DateType::Deadline, $preferences->getDateType());

        $orders = $entityManager
            ->getRepository(Order::class)
            ->getByOrdersPreferences($user->getPreferences()->getOrdersPreferences(), $rows);

        $client->loginUser($user);
        $crawler = $client->request('GET', '/orders');

        $this->assertCount(8, $orders);
        $rowCountContent = $crawler->filter('div.mid-col .col-content .rows-count span')->text();
        $this->assertEquals('8/8', $rowCountContent);
    }
}
