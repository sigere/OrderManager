<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Log;
use App\Entity\Order;
use App\Entity\User;
use App\Entity\Staff;
use App\Form\AddOrderForm;
use App\Form\IndexFiltersForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Validator\Constraints\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(EntityManagerInterface $entityManager, Request $request, UrlGeneratorInterface $urlGenerator): Response
    {
        
        $user = $this->getUser();
        $repo = $entityManager->getRepository(Order::class);
        $orders = $this->loadUserOrdersTable();
        
        $form = $this->createForm(IndexFiltersForm::class);
        $form->handleRequest($request);

        return $this->render('index/index.html.twig', [
            'orders' => $orders,
            'filtersForm' => $form->createView(),
        ]);
    }


    /**
     * @Route("/index/api/filters", name="index_api_filters")
     */
    public function indexApiFilters(EntityManagerInterface $entityManager, Request $request): Response
    {
        // sleep(0.8);
        $form = $this->createForm(IndexFiltersForm::class);
        $form->handleRequest($request);
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $preferences = $user->getPreferences();
            $preferences['index'] = $form->getData();
            $preferences['index']['select-client'] = $preferences['index']['select-client'] ? $preferences['index']['select-client']->getId(): null;
            $user->setPreferences($preferences);
            $entityManager->persist($user);
            $entityManager->flush();
        }
        
        return new Response(' return reached ');
    }


    /**
     * @Route("/index/api/reloadTable", name="index_reload_table")
     */
    public function reloadTable() : Response
    {
        $orders = $this->loadUserOrdersTable();
        return $this->render('index/orders_table.twig', [
            'orders' => $orders
        ]);
    }


    private function loadUserOrdersTable()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(Order::class);
        $user = $this->getUser();
        $preferences = $user->getPreferences();
        // dd($preferences);
        $states = [];
        if ($preferences['index']['przyjete']) $states[] = 'przyjete';
        if ($preferences['index']['wykonane']) $states[] = 'wykonane';
        if ($preferences['index']['wyslane']) $states[] = 'wyslane';
        if ($preferences['index']['rozliczone']) $states[] = 'rozliczone';

        $statesString = 'o.state = ';
        foreach ($states as $s) {
            $statesString .= ':' . $s . ' or o.state = ';
        }
        $statesString = substr($statesString, 0, -14);

        $repo = $entityManager->getRepository(Order::class);
        $orders = $repo->createQueryBuilder('o')
            ->andWhere('o.staff = :staff')
            ->setParameter('staff', $user->getStaff())
            ->andWhere($statesString);

        foreach ($states as $s) {
            $orders = $orders->setParameter($s, $s);
        }

        if (!$preferences['index']['usuniete'])
            $orders = $orders->andWhere('o.deletedAt is null');

        if($preferences['index']['select-client'])
            $orders = $orders
                ->andWhere('o.client = :client')
                ->setParameter('client', $repository->findOneBy(['id' => $preferences['index']['select-client'] ]));
                //doctrine nie zapisuje obiektów w user->preferences['index']['select-client'],
                //więc mapuje na id przy zapisie i na obiekt przy odczycie 
        
        $orders = $orders
            ->setMaxResults(400)
            ->orderBy('o.deadline', 'ASC')
            ->getQuery()
            ->getResult();
            // dd($orders);
        return $orders;
    }

    //-----------------------Development-bajzel-DO-NOT-READ-------------------

    /**
     * @Route("/addOrder", name="addOrder")
     */
    public function addOrder(Request $request)
    {
        $form = $this->createForm(AddOrderForm::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            dd($data);
            $order = new Order();
            $order->setTopic($data['topic']);
        }

        return $this->render('index/addOrder.html.twig', [
            'addOrderForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/fix", name="fix")
     */
    public function fix(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
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

        // $user = $this->getUser();
        // $this->getUser()->__construct();
        // $entityManager->flush($user);
        // dd($entityManager->getRepository(User::class)->findOneBy(['id' => "1"]));


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
        // $entityManager->persist($user);
        // $entityManager->flush();

        //return new Response('<h3>Done</h3>');
        return $this->render('settingsPopup.html.twig');
    }
}
