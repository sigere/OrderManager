<?php

namespace App\Command;

use App\Entity\CertifiedOrder;
use App\Entity\Client;
use App\Entity\Company;
use App\Entity\Lang;
use App\Entity\Log;
use App\Entity\Order;
use App\Entity\RepertoryEntry;
use App\Entity\Staff;
use App\Entity\User;
use App\Repository\RepertoryEntryRepository;
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
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $staff = new Staff();
        $staff = $staff
            ->setFirstName('Administrator')
            ->setLastName('Administrative');
        $this->entityManager->persist($staff);
        $io->info('Created staff pearson.');

        $user = new User();
        $user = $user
            ->setUsername('admin')
            ->setFirstName('Admin')
            ->setLastName('Admin')
            ->setStaff($staff)
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN'])
            ->setPassword('$argon2id$v=19$m=16,t=2,p=1$UjBOZUdsZ3E0RFc1U3BqTw$lwY1VMjdwlDL+37w8jjrBA');
        $this->entityManager->persist($user);
        $io->info('Created user admin:admin.');

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
        $io->info('Created sample company.');

        $lang = new Lang();
        $lang = $lang
            ->setName('Polski')
            ->setShort('PL');
        $this->entityManager->persist($lang);
        $io->info('Created language: Polski.');

        $lang1 = new Lang();
        $lang1 = $lang1
            ->setName('Angielski')
            ->setShort('EN');
        $this->entityManager->persist($lang1);
        $io->info('Created language: Angielski.');

        $lang2 = new Lang();
        $lang2 = $lang2
            ->setName('Ukraiński')
            ->setShort('UA');
        $this->entityManager->persist($lang2);
        $io->info('Created language: Ukraiński.');

        $client = new Client();
        $client = $client
            ->setName('Przykładowy firma s.c.')
            ->setNip('1231231231')
            ->setPostCode('00000')
            ->setCity('Warszawa')
            ->setStreet('ul. Wiejska 123')
            ->setEmail('e-mail@email.com')
            ->setCountry('PL')
            ->setAlias('client');
        $this->entityManager->persist($client);
        $io->info('Created sample client.');

        $order = new Order();
        $order = $order
            ->setStaff($staff)
            ->setAuthor($user)
            ->setDeadline(new DateTime())
            ->setTopic('Przykładowy temat zlecenia')
            ->setPages(4)
            ->setInfo('Dodatkowe info o zleceniu')
            ->setAdoption(new DateTime())
            ->setCertified(false)
            ->setBaseLang($lang)
            ->setTargetLang($lang2)
            ->setPrice(30)
            ->setClient($client);
        $this->entityManager->persist($order);
        $io->info('Created sample order.');

        $repertoryEntry = new RepertoryEntry(
            $this->entityManager->getRepository(RepertoryEntry::class),
            $order
        );

        $repertoryEntry = $repertoryEntry
            ->setComments("Comments")
            ->setCopies(2)
            ->setCopyPrice(10)
            ->setDocumentDate(new \DateTime())
            ->setOrder($order);
        $this->entityManager->persist($repertoryEntry);
        $io->info('Created sample certified order.');

        $this->entityManager->persist(
            new Log($user, 'Dodano wpis do repertorium.', $repertoryEntry)
        );
        $io->info('Created log for order.');

        $this->entityManager->flush();

        $io->success('Installation completed.');

        return Command::SUCCESS;
    }
}
