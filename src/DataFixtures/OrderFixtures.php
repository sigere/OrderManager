<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Enum\OrderState;
use App\Entity\Order;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    private const string ORDER_DATA_FILE = __DIR__.'/orders.yaml';

    public function load(ObjectManager $manager): void
    {
        $parsed = Yaml::parseFile(self::ORDER_DATA_FILE);

        foreach ($parsed['orders'] as $orderData) {
            $order = (new Order())
                ->setClient($this->getReference('client_'.strtolower($orderData['client_alias'])))
                ->setAuthor($this->getReference('user_'.strtolower($orderData['user'])))
                ->setStaff($this->getReference('staff_'.strtolower($orderData['staff'])))
                ->setBaseLang($this->getReference('lang_'.strtolower($orderData['base_lang'])))
                ->setTargetLang($this->getReference('lang_'.strtolower($orderData['target_lang'])))
                ->setCertified($orderData['certified'])
                ->setPages($orderData['pages'])
                ->setPrice($orderData['price'])
                ->setAdditionalFee($orderData['additional_fee'])
                ->setTopic($orderData['topic'])
                ->setState(OrderState::tryFrom($orderData['state'] ?? null) ?? OrderState::Accepted)
                ->setInfo($orderData['info'])
                ->setAdoption(new \DateTime($orderData['adoption']))
                ->setDeadline(new \DateTime($orderData['deadline']))
                ->setSettledAt(isset($orderData['settled_at']) ? new \DateTime($orderData['settled_at']) : null)
                ->setDeletedAt(isset($orderData['deleted_at']) ? new \DateTime($orderData['deleted_at']) : null);

            $manager->persist($order);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ClientFixtures::class,
            AppFixtures::class,
            StaffFixtures::class,
            UserFixtures::class,
        ];
    }
}
