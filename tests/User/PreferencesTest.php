<?php

declare(strict_types=1);

namespace App\Tests\User;

use App\Entity\User;
use App\Preferences\Model\DateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PreferencesTest extends WebTestCase
{
    public function testUserHasDefaultPreferencesInitially(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => 'tester1']);

        $this->assertInstanceOf(User::class, $user);

        $fromDb = $entityManager->createQueryBuilder()
            ->select('u.preferences')
            ->from(User::class, 'u')
            ->where('u.username = :username')
            ->setParameter('username', $user->getUsername())
            ->getQuery()
            ->getSingleScalarResult();

        $this->assertNull($fromDb);

        $orderPreferences = $user->getPreferences()->getOrdersPreferences();
        $this->assertNotNull($orderPreferences);

        $this->assertNull($orderPreferences->getStaff());
        $this->assertNull($orderPreferences->getClient());
        $this->assertEquals(['accepted', 'done', 'sent'], $orderPreferences->getStates());
        $this->assertFalse($orderPreferences->getDeleted());
        $this->assertFalse($orderPreferences->getSettled());
        $this->assertNull($orderPreferences->getDateTo());
        $this->assertNull($orderPreferences->getDateFrom());
        $this->assertEquals(DateType::Deadline, $orderPreferences->getDateType());
    }

    /**
     * @dataProvider provideInvalidPreferencesData
     */
    public function testPreferencesAreValidatedCorrectly(array $data, callable $makeAssertions): void
    {
        $client = static::createClient(['HTTP_ACCEPT_LANGUAGE' => 'en']);
        $user = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(User::class)
            ->findOneBy(['username' => 'tester1']);

        $client->loginUser($user);
        $crawler = $client->request('GET', '/orders');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="order_filters_form"]');

        $tokenValue = $crawler->filter('input[name="order_filters_form[_token]"]')->attr('value');
        $client->request('PUT', '/preferences/filters/orders', [
            'order_filters_form' => [...$data, '_token' => $tokenValue],
        ]);

        $makeAssertions($client);
    }

    public function testPreferencesAreSavedOnFormSubmit(): void
    {
        $client = static::createClient(['HTTP_ACCEPT_LANGUAGE' => 'en']);
        $entityManager = $client->getContainer()->get(EntityManagerInterface::class);
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => 'tester1']);

        $client->loginUser($user);
        $client->request('GET', '/orders');

        $this->assertSelectorExists('form[name="order_filters_form"]');

        $client->submitForm(
            'Execute', [
                'order_filters_form' => [
                    'dateFrom' => '2024-01-01',
                    'dateTo' => '2024-01-02',
                ],
            ], 'PUT'
        );

        $this->assertResponseIsSuccessful();

        // $container (whole kernel as well probably) changed after submitting form
        $entityManager = $client->getContainer()->get(EntityManagerInterface::class);
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => 'tester1']);
        $orderPreferences = $user->getPreferences()->getOrdersPreferences();

        $this->assertNotNull($orderPreferences->getDateFrom());
        $this->assertNotNull($orderPreferences->getDateTo());
        $this->assertEquals('2024-01-01', $orderPreferences->getDateFrom()->format('Y-m-d'));
        $this->assertEquals('2024-01-02', $orderPreferences->getDateTo()->format('Y-m-d'));
    }

    public function provideInvalidPreferencesData(): array
    {
        return [
            [
                [
                    'columns' => [
                        'INVALID_FIELD' => 1,
                    ],
                    'client' => '',
                    'staff' => '',
                    'dateType' => 'deadline',
                    'dateFrom' => '',
                    'dateTo' => '',
                ],
                function (KernelBrowser $client) {
                    $this->assertResponseStatusCodeSame(400);
                    $this->assertStringContainsString(
                        'should not contain extra fields', $client->getResponse()->getContent()
                    );
                },
            ],
            [
                [
                    'columns' => [],
                    'client' => '',
                    'staff' => '',
                    'dateType' => 'deadline',
                    'dateFrom' => '2024-01-02',
                    'dateTo' => '2024-01-01',
                ],
                function (KernelBrowser $client) {
                    $this->assertResponseStatusCodeSame(400);
                    $this->assertStringContainsString(
                        'must be before the date to', $client->getResponse()->getContent()
                    );
                    $this->assertStringContainsString(
                        'must be after the date from', $client->getResponse()->getContent()
                    );
                },
            ],
        ];
    }
}
