<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    private const array USERS = [
        [
            'username' => 'admin',
            'firstName' => 'admin',
            'lastName' => 'admin',
            'roles' => ['ROLE_USER', 'ROLE_ADMIN'],
            'password' => 'admin',
            'createdAt' => 1,
        ],
        [
            'username' => 'tester_1',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'roles' => ['ROLE_USER'],
            'password' => '12345',
            'createdAt' => 1672531199,
        ],
        [
            'username' => 'tester_2',
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'roles' => ['ROLE_ADMIN'],
            'password' => '67890',
            'createdAt' => null,
        ],
    ];

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::USERS as $userData) {
            $staff = null;
            if ('John' === $userData['firstName'] && 'Doe' === $userData['lastName']) {
                $staff = $this->getReference(StaffFixtures::JOHN_DOE);
            }

            $createdAt = new \DateTime();
            if ($userData['createdAt']) {
                $createdAt->setTimestamp($userData['createdAt']);
            }

            $user = (new User())
                ->setUsername($userData['username'])
                ->setFirstName($userData['firstName'])
                ->setLastName($userData['lastName'])
                ->setRoles($userData['roles'])
                ->setPreferences(null)
                ->setStaff($staff)
                ->setCreatedAt($createdAt)
                ->setDeletedAt(null);

            $user->setPassword(
                $this->hasher->hashPassword($user, $userData['password'])
            );

            $this->addReference('user_'.$userData['username'], $user);

            $manager->persist($user);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            StaffFixtures::class,
        ];
    }
}
