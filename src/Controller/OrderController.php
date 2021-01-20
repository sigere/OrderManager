<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Log;
use App\Entity\Order;
use App\Entity\User;

class OrderController extends AbstractController
{
    /**
     * @Route("/zlecenie/{id}", name="order")
     */
    public function order(Order $order, EntityManagerInterface $em)
    {
        if (!$order) {
            throw $this->createNotFoundException('Nie znaleziono zlecenia');
        }
        $logRepo = $em->getRepository(Log::class);
        $logs = $logRepo->findBy(['order' => $order]);
        return $this->render('order/index.html.twig', [
            'order' => $order,
            'logs' => $logs,
        ]);
    }
}
