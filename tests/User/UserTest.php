<?php

declare(strict_types=1);

namespace App\Tests\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTest extends WebTestCase
{
    public function testAnonymousUserIsRedirectedToLogin(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');
        $this->assertResponseRedirects('/login');

        $client->request('GET', '/orders');
        $this->assertResponseRedirects('/login');
    }

    public function testUserExists(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $user = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'tester1']);

        $this->assertNotNull($user);
    }

    public function testLoggedUserSeesOrders(): void
    {
        $client = static::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $user = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'tester1']);

        $client->loginUser($user);
        $client->request('GET', '/orders');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table[data-controller="table"]');
    }
}
