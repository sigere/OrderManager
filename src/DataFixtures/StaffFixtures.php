<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Staff;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class StaffFixtures extends Fixture
{
    public const string JOHN_DOE = 'staff_john_doe';

    private const array STAFF = [
        [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'createdAt' => 1672531199,
        ],
        [
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'createdAt' => 1672531199,
        ],
        [
            'firstName' => 'Alice',
            'lastName' => 'Johnson',
            'createdAt' => 1672531199,
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::STAFF as $staffData) {
            $createdAt = new \DateTime();
            $createdAt->setTimestamp($staffData['createdAt']);

            $staff = (new Staff())
                ->setFirstName($staffData['firstName'])
                ->setLastName($staffData['lastName'])
                ->setCreatedAt($createdAt);

            $manager->persist($staff);

            $this->addReference(sprintf(
                'staff_%s_%s',
                strtolower($staffData['firstName']),
                strtolower($staffData['lastName']),
            ), $staff);
        }

        $manager->flush();
    }
}
