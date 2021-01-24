<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Order;
use App\Entity\User;
use App\Entity\Staff;
use App\Form\AddOrderForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $orders = $repo->getActive()
            ->andWhere('o.staff = :staff')
            ->setParameter('staff', $user->getStaff())
            ->setMaxResults(100)
            ->orderBy('o.deadline', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        $form = $this->createForm(AddOrderForm::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $order = $form->getData();
            $order->setAuthor($this->getUser());
            $log = new Log($user,"Dodano zlecenie",$order);
            $entityManager->persist($order);
            $entityManager->persist($log);
            $entityManager->flush();
            return new RedirectResponse($urlGenerator->generate('order',['id' => $order->getId()]));
        }
        
        return $this->render('index/index.html.twig', [
            'orders' => $orders,
            'addOrderForm'=> $form->createView(),
        ]);
    }
//-----------------------Development-bajzel-DO-NOT-READ-------------------

    /**
     * @Route("/addOrder", name="addOrder")
     */
    public function addOrder(Request $request){
        $form = $this->createForm(AddOrderForm::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            dd($data);
            $order = new Order();
            $order->setTopic($data['topic']);
        }

        return $this->render('index/addOrder.html.twig', [
            'addOrderForm'=> $form->createView(),
        ]);
    }

    /**
     * @Route("/fix", name="fix")
     */
    public function fix(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        //$staffRepo = $entityManager->getRepository(Staff::class);
        // $orderRepo = $entityManager->getRepository(Order::class);
        // $order = $orderRepo->findOneBy(['id' => 1]);
        //$staff = $staffRepo->findOneBy(['id' => '1']);

        // $user->setPreferences([
        //     'indexColumns' => ['id', 'topic', 'state'],
        // ]);

        // $log = new Log($user, ['add'], $order);

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
