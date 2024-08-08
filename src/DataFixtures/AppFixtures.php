<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Lang;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public const string COMPANY_REFERENCE = 'company';
    public const array LANGS = [
        [
            'name' => 'English',
            'short' => 'EN',
        ],
        [
            'name' => 'Polish',
            'short' => 'PL',
        ],
        [
            'name' => 'Italian',
            'short' => 'IT',
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        $this->loadCompany($manager);
        $this->loadLang($manager);

        $manager->flush();
    }

    private function loadCompany(ObjectManager $manager): void
    {
        // todo move to user preferences
        $issueDate = new \DateTime();
        $issueDate->setTimestamp(1672531199);

        $paymentTo = new \DateTime();
        $paymentTo->setTimestamp(1672531199);

        $invoiceMonth = new \DateTime();
        $invoiceMonth->setTimestamp(1672531199);

        $company = (new Company())->setName('Dummy Company inc.')
            ->setNip('1234567890')
            ->setAddress('123 Main St')
            ->setPostCode('12-345')
            ->setCity('Sample City')
            ->setBankAccount('12345678901234567890123456')
            ->setIssueDate($issueDate)
            ->setPaymentTo($paymentTo)
            ->setRep('123')
            ->setInvoiceMonth($invoiceMonth);

        $this->addReference(self::COMPANY_REFERENCE, $company);

        $manager->persist($company);
    }

    private function loadLang(ObjectManager $manager): void
    {
        foreach (self::LANGS as $langData) {
            $lang = (new Lang())
                ->setName($langData['name'])
                ->setShort($langData['short']);

            $this->addReference(sprintf('lang_%s', strtolower($langData['short'])), $lang);

            $manager->persist($lang);
        }
    }
}
