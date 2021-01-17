<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Order;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(EntityManagerInterface $entityManager): Response
    {
        $userRepo = $entityManager->getRepository(User::class);
        $user = $userRepo->findOneBy(['id' => '1']);
        $repo = $entityManager->getRepository(Order::class);
        $orders = $repo->getActive()
            ->andWhere('o.staff = '.$user->getStaff()->getId())
            ->setMaxResults(100)
            ->orderBy('o.deadline', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        $tmp = new Order();

        return $this->render('index/index.html.twig', [
            'user' => $user,
            'orders' => $orders,
            'colToDisplay' => $user->getPreferences()['indexColumns'],
            'allColumns' => $tmp->getAllColumns(),
        ]);
    }

    /**
     * @Route("/zlecenie/{id}")
     */
    public function order($id, EntityManagerInterface $entityManager)
    {
        $orderRepo = $entityManager->getRepository(Order::class);
        $order = $orderRepo->findOneBy(['id' => $id]);

        if (!$order) {
            throw $this->createNotFoundException('Nie znaleziono zlecenia');
        }

        $logRepo = $entityManager->getRepository(Log::class);
        $logs = $logRepo->findBy(['order' => $order]);

        $userRepo = $entityManager->getRepository(User::class);
        $user = $userRepo->findOneBy(['id' => '1']);

        return $this->render('index/order.html.twig', [
            'order' => $order,
            'logs' => $logs,
            'user' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/fix", name="fix")
     */
    public function fix(EntityManagerInterface $entityManager)
    {
        $userRepo = $entityManager->getRepository(User::class);
        $orderRepo = $entityManager->getRepository(Order::class);
        $order = $orderRepo->findOneBy(['id' => 1]);
        $user = $userRepo->findOneBy(['id' => '1']);

        $user->setPreferences([
            'indexColumns' => ['id', 'topic', 'state'],
        ]);

        $log = new Log($user, ['add'], $order);

        $entityManager->persist($log);
        $entityManager->flush();

        return new Response('<h3>Done</h3>');
    }
}
