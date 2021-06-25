<?php

namespace App\Command;

use App\Entity\Company;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InstallSampleDataCommand extends Command
{
    protected static $defaultName = 'app:installSampleData';
    protected static $defaultDescription = 'Add a short description for your command';

    public function __construct(
        private EntityManagerInterface $entityManager,
        string $name = null
    )
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $user = new User();
        $user = $user
            ->setUsername('admin')
            ->setFirstName('Admin')
            ->setLastName('Admin')
            ->setRoles(["ROLE_USER", "ROLE_ADMIN"])
            ->setPassword('$argon2id$v=19$m=16,t=2,p=1$UjBOZUdsZ3E0RFc1U3BqTw$lwY1VMjdwlDL+37w8jjrBA');
        $this->entityManager->persist($user);
        $io->success("Created user admin:admin");

        $company = new Company();
        $company = $company
            ->setName('Moja firma')
            ->setAddress('ul. Krakowska 112')
            ->setCity('Warszawa')
            ->setPostCode('00000')
            ->setRep('notatka - kliknij mnie')
            ->setNip('123456789')
            ->setBankAccount('12345678909234456234')
            ->setPaymentTo(new DateTime())
            ->setIssueDate(new DateTime())
            ->setInvoiceMonth(new DateTime());
        $this->entityManager->persist($company);
        $io->success("Created sample company");

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
