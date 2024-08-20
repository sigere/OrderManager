<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ClientFixtures extends Fixture
{
    private const array CLIENTS = [
        [
            'name' => 'Client A',
            'alias' => 'client_a',
            'nip' => '1234567890',
            'postCode' => '00-001',
            'city' => 'City A',
            'street' => '123 Main St',
            'country' => 'PL',
            'createdAt' => 1672531199,
            'deletedAt' => null,
            'email' => 'clienta@example.com',
        ],
        [
            'name' => 'Client B',
            'alias' => 'client_b',
            'nip' => '0987654321',
            'postCode' => '00-002',
            'city' => 'City B',
            'street' => '456 Elm St',
            'country' => 'PL',
            'createdAt' => 1672531199,
            'deletedAt' => null,
            'email' => 'clientb@example.com',
        ],
        [
            'name' => 'Client C',
            'alias' => 'client_c',
            'nip' => '5555555555',
            'postCode' => '00-003',
            'city' => 'City C',
            'street' => '789 Oak St',
            'country' => 'PL',
            'createdAt' => 1672531198,
            'deletedAt' => 1672531199,
            'email' => 'clientc@example.com',
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::CLIENTS as $clientData) {
            $createdAt = new \DateTime();
            $createdAt->setTimestamp($clientData['createdAt']);

            $deletedAt = null;
            if ($clientData['deletedAt']) {
                $deletedAt = new \DateTime();
                $deletedAt->setTimestamp($clientData['deletedAt']);
            }

            $client = (new Client())
                ->setName($clientData['name'])
                ->setAlias($clientData['alias'])
                ->setNip($clientData['nip'])
                ->setPostCode($clientData['postCode'])
                ->setCity($clientData['city'])
                ->setStreet($clientData['street'])
                ->setCountry($clientData['country'])
                ->setCreatedAt($createdAt)
                ->setDeletedAt($deletedAt)
                ->setEmail($clientData['email']);

            $manager->persist($client);

            $this->addReference('client_'.strtolower($clientData['alias']), $client);
        }

        $manager->flush();
    }
}
