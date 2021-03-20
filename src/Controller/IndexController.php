<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Company;
use App\Entity\Lang;
use App\Entity\Log;
use App\Entity\Order;
use App\Entity\Staff;
use App\Entity\User;
use App\Form\AddOrderForm;
use App\Form\IndexFiltersForm;
use Datetime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Date;

class IndexController extends AbstractController
{
    private $entityManager;
    private $request;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $request)
    {
        $this->entityManager = $entityManager;
        $this->request = $request->getCurrentRequest();
    }

    /**
     * @Route("/", name="index")
     * @return Response
     */
    public function index(): Response
    {
        $orders = $this->loadOrdersTable();
        $form = $this->createForm(IndexFiltersForm::class);

        return $this->render('index/index.html.twig', [
            'orders' => $orders,
            'filtersForm' => $form->createView(),
        ]);
    }

    private function loadOrdersTable() //improvements required
    {
        $repository = $this->entityManager->getRepository(Order::class);
        $user = $this->getUser();
        $preferences = $user->getPreferences();
        $states = [];
        if ($preferences['index']['przyjete']) $states[] = 'przyjete';
        if ($preferences['index']['wykonane']) $states[] = 'wykonane';
        if ($preferences['index']['wyslane']) $states[] = 'wyslane';

        if (!count($states) > 0)
            return [];

        $statesString = 'o.state = ';
        foreach ($states as $s) {
            $statesString .= ':' . $s . ' or o.state = ';
        }
        $statesString = substr($statesString, 0, -14);

        $repo = $this->entityManager->getRepository(Order::class);
        $staff = $this->entityManager->getRepository(Staff::class)->findOneBy(['id' => $preferences['index']['select-staff']]);
        $orders = $repo->createQueryBuilder('o')
            ->andWhere($statesString);

        if ($preferences['index']['select-staff']) {
            $orders = $orders
                ->andWhere('o.staff = :staff')
                ->setParameter('staff', $staff ? $staff : $this->getUser());
        }

        foreach ($states as $s) {
            $orders = $orders->setParameter($s, $s);
        }


        if ($preferences['index']['select-client'])
            $orders = $orders
                ->andWhere('o.client = :client')
                ->setParameter('client', $repository->findOneBy(['id' => $preferences['index']['select-client']]));
        //doctrine nie zapisuje obiektów w user->preferences['index']['select-client'],
        //więc mapuje na id przy zapisie i na obiekt przy odczycie

        $dateType = $preferences['index']['date-type'];
        if ($preferences['index']['date-from']) {
            $dateFrom = new Datetime($preferences['index']['date-from']['date']);
            $orders
                ->andWhere('o.' . $dateType . ' >= :dateFrom')
                ->setParameter('dateFrom', $dateFrom);
        }
        if ($preferences['index']['date-to']) {
            $dateTo = new Datetime($preferences['index']['date-to']['date']);
            $dateTo->setTime(23, 59);
            $orders
                ->andWhere('o.' . $dateType . ' <= :dateTo')
                ->setParameter('dateTo', $dateTo);
        }

        $orders = $orders
            ->andWhere('o.settledAt is null')
            ->andWhere('o.deletedAt is null')
            ->setMaxResults(100)
            ->orderBy('o.deadline', 'ASC')
            ->getQuery()
            ->getResult();
        return $orders;
    }

    /**
     * @Route("/index/api/filters", name="index_api_filters")
     * @return Response
     */
    public function indexApiFilters(): Response
    {
        $form = $this->createForm(IndexFiltersForm::class);
        $form->handleRequest($this->request);
        $user = $this->getUser();
        if ($form->isSubmitted() && $form->isValid()) {
            $preferences = $user->getPreferences();
            $preferences['index'] = $form->getData();
            $preferences['index']['select-staff'] = $preferences['index']['select-staff'] ? $preferences['index']['select-staff']->getId() : null;
            $preferences['index']['select-client'] = $preferences['index']['select-client'] ? $preferences['index']['select-client']->getId() : null;
            $user->setPreferences($preferences);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return new Response("Zastosowano filtry.", 200);
        }

        return new Response('Błędne dane.', 400);
    }

    /**
     * @Route("/index/api/reloadTable", name="index_api_reloadTable")
     */
    public function reloadTable(): Response
    {
        $orders = $this->loadOrdersTable();
        return $this->render('index/orders_table.twig', [
            'orders' => $orders
        ]);
    }

    /**
     * @Route("/index/api/details/{id}", name="index_api_details")
     * @param Order $order
     * @return Response
     */
    public function details(Order $order): Response
    {
        if (!$order) {
            throw $this->createNotFoundException('Nie znaleziono zlecenia');
        }
        $logs = $this->entityManager->getRepository(Log::class)->findBy(['order' => $order], ['createdAt' => 'DESC'],100);

        return $this->render('index/details.twig', [
            'order' => $order,
            'logs' => $logs
        ]);
    }

    /**
     * @Route("/index/api/updateState/{id}/{state}", name="index_updateState")
     * @param Order $order
     * @param $state
     * @return Response
     */
    public function updateState(Order $order, $state): Response
    {
        //TODO autoryzacja?
        if ($order->getState() == $state)
            return new Response('state not changed');

        $currentState = $order->getState();
        switch ($state) {
            case $order::WYSLANE:
            case $order::WYKONANE:
            case $order::PRZYJETE:
                $order->setState($state);
                break;
            default:
                return new Response("given state not found", 404);
        }
        $this->entityManager->persist(new Log(
            $this->getUser(),
            "Zmiana statusu: " . $currentState . " -> " . $state . ".", $order
        ));
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        return new Response('Zmieniono status', 200);
    }

    /**
     * @Route("/index/api/deleteOrder/{id}", name="index_api_deleteOrder")
     * @param Order $order
     * @return Response
     */
    public function deleteOrder(Order $order): Response
    {
        if ($order->getDeletedAt())
            return new Response("Zlecenie zostało już usunięte", 406);

        $order->setDeletedAt(new Datetime());
        $this->entityManager->persist($order);
        $this->entityManager->persist(new Log($this->getUser(), "Usunięto zlecenie", $order));
        $this->entityManager->flush();
        return new Response("Zlecenie usunięte", 200);
    }

    /**
     * @Route("/index/api/addOrder", name="index_api_addorder")
     * @param Request $request
     * @return Response
     */
    public function addOrder(Request $request): Response
    {
        $form = $this->createForm(AddOrderForm::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $order = $form->getData();
            $order->setAuthor($this->getUser());

            $this->entityManager->persist($order);
            $this->entityManager->persist(new Log($this->getUser(), "Dodano zlecenie", $order));
            $this->entityManager->flush();
            return new Response("Dodano zlecenie", 201, ["orderId" => $order->getId()]);
        }

        return $this->render('index/addOrder.html.twig', [
            'addOrderForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/index/api/updateOrder/{id}", name="index_api_updateOrder")
     * @param Order $order
     * @return Response
     */
    public function updateOrder(Order $order): Response
    {
        $form = $this->createForm(AddOrderForm::class, $order);

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $order = $form->getData();
            $this->entityManager->persist($order);
//            TODO sprawdzanie co się zmieniło i logowanie tego
            $this->entityManager->persist(new Log($this->getUser(), "Zaktualizowano zlecenie", $order));
            $this->entityManager->flush();
            return new Response("Zaktualizowano zlecenie.", 202, ["orderId" => $order->getId()]);
        }

        return $this->render('index/addOrder.html.twig', [
            'addOrderForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/index/api/settle/{id}", name="index_api_settle")
     * @param Order $order
     * @return Response
     */
    public function settle(Order $order): Response
    {
        if (count($order->getWarnings()))
            return new Response("Zlecenie nie może zostać rozliczone", 406);
        if ($order->getSettledAt())
            return new Response("Zlecenie zostało już rozliczone.", 406);
        $order->setSettledAt(new Datetime());
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        return new Response("Rozliczono zlecenie", 200);
    }

    //-----------------------Development-Tools-------------------

    /**
     * @Route("/dumpRequest", name="index_dump_request")
     */
    public function dumpRequest(): Response
    {
        dump($this->request);
        dump($_POST);
        return new Response("",200);
    }

    /**
     * @Route("/fix", name="fix")
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function fix(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder): Response
    {
//        $staff = new Staff();
//        $staff->setFirstName("Papieżyca");
//        $staff->setLastName("Joanna");
//        $user = new User();
//        $user->setStaff($staff);
//        $user->setFirstName("Daria");
//        $user->setLastName("Łuka");
//        $user->setUsername("daria");
//        $user->setPassword('$argon2id$v=19$m=16,t=2,p=1$OEp3ZHU3bmxUQ1JPd1Q5bA$e/YQwUHwiA+rxAsTOebLxg');
//
//        $this->entityManager->persist($staff);
//        $this->entityManager->persist($user);
//        $this->entityManager->flush();
//
//        return new Response("ok", 200);

        $now = new Datetime();
//        $tmp = new Datetime();
//        $tmp->setTimestamp($now->getTimestamp()+10000000);
//        dump($tmp);
        $words = explode(" ",
            "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin ullamcorper porttitor gravida. Quisque a massa facilisis, pellentesque metus sed, luctus ipsum. Etiam ut neque eros. Morbi leo metus, auctor ut porta in, malesuada at augue. Praesent semper odio orci, in consectetur velit rutrum nec. Nam vitae massa porta metus pulvinar sagittis. Aliquam tincidunt congue arcu sit amet malesuada."
        );
        $langs = $this->entityManager->getRepository(Lang::class)->findAll();
        $staff = $this->entityManager->getRepository(Staff::class)->findAll();
        $clients = $this->entityManager->getRepository(Client::class)->findAll();
        for ($i = 0; $i < 100; $i++){
            $order = new Order();
            $order->setAuthor($this->getUser());
            $order->setAdoption(new Datetime());
            $order->setBaseLang($langs[rand(0,sizeof($langs)-1)]);
            $order->setTargetLang($langs[rand(0,sizeof($langs)-1)]);
            $order->setStaff($staff[rand(0,sizeof($staff)-1)]);
            $order->setCertified(rand(0,1) ? true : false);
            $order->setInfo($words[rand(0,sizeof($words)-1)]." ".$words[rand(0,sizeof($words)-1)]);
            $order->setTopic($words[rand(0,sizeof($words)-1)]." ".$words[rand(0,sizeof($words)-1)]." ".$words[rand(0,sizeof($words)-1)]);
            $order->setPages(max(rand(-5, 20),0));
            $order->setPrice(max(rand(-5, 20),0));
            $order->setClient($clients[rand(0,sizeof($clients)-1)]);

            $stamp = rand($now->getTimestamp(), $now->getTimestamp()+10000000);
            $dt = new Datetime();
            $dt->setTimestamp($stamp);

            $order->setDeadline($dt);
//            dump($order);
            $this->entityManager->persist($order);
        }
        $this->entityManager->flush();
        return new Response("ok", 200);


        $company = $entityManager->getRepository(Company::class)->findAll()[0];
        $company->setAddress("ul. Miodowa 112");
        $company->setName("Biuro Tłumaczeń Kadamaxo");
        $company->setCity("Poznań");
        $company->setBankAccount("123132123123");
        $company->setNip("1234567890");
        $company->setPostCode("32-109");
        $company->setIssueDate(new Datetime());
        $company->setPaymentTo(new Datetime());
        $entityManager->persist($company);
        $entityManager->flush();
        dd($company);
        // $order = $entityManager->getRepository(Order::class)->findOneBy(['id' => 1]);
//        $log = new Log($this->getUser(),"crazy shit just happend", new Order());
//        dump($log);
//        $log = new Log($this->getUser(),"crazy shit just happend", new Client());
//        dump($log);
//        die("ok");
        // $entityManager->persist($log);
        // $entityManager->flush();
        // dd($log);

        // $client = new Client();
        // $client->setName("W11 wydział kryminalny");
        // $client->setAlias("W11");
        // $client->setNip("123-456-78-90");
        // $client->setPostCode("asd");
        // $client->setCity("Jarosław");
        // $client->setStreet("ul. Poniatwoskiego 997");
        // $client->setCountry("PL");
        // $entityManager->persist($client);
        // $entityManager->flush();
        // dd($client);

        $user = $this->getUser();
        $user->__construct();
        $entityManager->persist($user);
        $entityManager->flush();
        dd($user);

//         $entityManager->getRepository(User::class)->findOneBy(['id' => "1"]));


        //$staffRepo = $entityManager->getRepository(Staff::class);
        // $orderRepo = $entityManager->getRepository(Order::class);
        // $order = $orderRepo->findOneBy(['id' => 1]);
        //$staff = $staffRepo->findOneBy(['id' => '1']);

        // $user->setPreferences([
        //     'indexColumns' => ['id', 'topic', 'state'],
        // ]);

        // $log = new Log($user, ['add'], $order);
        // $staff = new Staff();
        // $staff->setFirstName("Jan");
        // $staff->setLastName("Borówa");
        // $user = new User();
        // $user->setFirstName("siger");
        // $user->setLastName("siger");
        // $user->setUsername("siger");
        // $user->setRoles(['ROLE_USER', "ROLE_ADMIN"]);
        // $user->setPassword($passwordEncoder->encodePassword($user, "admin123"));
        // $user->setStaff($staff);
        // dd($user);
//        $entityManager->persist($user);
        // $entityManager->flush();

        //return new Response('<h3>Done</h3>');
        return $this->render('settingsPopup.html.twig');
    }
}
