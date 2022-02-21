<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Log;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    private Company $company;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        $this->company = $entityManager->getRepository(Company::class)->findAll()[0];
    }

    /**
     * @Route("/order/{id}/state", methods={"POST"}, name="api_order_post_state")
     */
    public function updateState(Request $request, Order $order): Response
    {
        if (!$state = $request->get('state')) {
            return new Response('Niepoprawne dane.', 400);
        }

        if ($order->getState() == $state) {
            return new Response('Zlecenie ma już podany status.', 400);
        }

        $currentState = $order->getState();
        if (!in_array($state, Order::STATES)) {
            return new Response('Nie znaleziono statusu.', 404);
        }

        $order->setState($state);
        $this->entityManager->persist(new Log(
            $this->getUser(),
            'Zmiana statusu: ' . $currentState . ' -> ' . $state . '.',
            $order
        ));
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return new Response('Zmieniono status.', 200);
    }

    /**
     * @Route("/order/{id}/settle", methods={"PUT"}, name="api_order_put_settle")
     */
    public function settleOrder(Order $order): Response
    {
        if (count($order->getWarnings())) {
            return new Response('Zlecenie nie może zostać rozliczone', 406);
        }

        if ($order->getSettledAt()) {
            return new Response('Zlecenie zostało już rozliczone.', 406);
        }

        $order->setSettledAt(new \Datetime());
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return new Response('Rozliczono zlecenie', 200);
    }

    /**
     * @Route("/company/{rep}", methods={"PUT"}, name="api_company_put_rep")
     */
    public function updateCompanyRep($rep): Response
    {
        $this->company->setRep($rep);
        $this->entityManager->persist($this->company);
        $this->entityManager->flush();

        return new Response('Wprowadzono zmiany', 200);
    }
}
