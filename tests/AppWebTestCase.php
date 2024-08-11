<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Client;
use App\Entity\Staff;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AppWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->getContainer()->get(EntityManagerInterface::class);
    }

    protected function getUser(?string $username = 'tester_1'): User
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);
    }

    protected function getStaff(string $firstName, string $lastName): Staff
    {
        return $this->entityManager
            ->getRepository(Staff::class)
            ->findOneBy(['firstName' => $firstName, 'lastName' => $lastName]);
    }

    protected function getAppClient(string $alias): Client
    {
        return $this->entityManager
            ->getRepository(Client::class)
            ->findOneBy(['alias' => $alias]);
    }
}
