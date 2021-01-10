<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QueriesController extends AbstractController
{
    /**
     * @Route("/kwerendy", name="queries")
     */
    public function index(): Response
    {
        return $this->render('queries/index.html.twig', [
            'controller_name' => 'QueriesController',
        ]);
    }
}
