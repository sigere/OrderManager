<?php

namespace App\Controller;

use App\Form\RepertoryFiltersForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RepertoryController extends AbstractController
{
    public function __construct()
    {

    }

    /**
     * @Route("/repertory", name="repertory")
     */
    public function index() : Response
    {
        $filtersForm = $this->createForm(RepertoryFiltersForm::class);

        return $this->render('repertory/index.html.twig', [
            'filtersForm' => $filtersForm->createView()
        ]);
    }
}