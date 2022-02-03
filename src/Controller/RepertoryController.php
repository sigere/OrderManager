<?php

namespace App\Controller;

use App\Entity\RepertoryEntry;
use App\Form\RepertoryFiltersForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RepertoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @Route("/repertory", name="repertory")
     */
    public function index(): Response
    {
        $filtersForm = $this->createForm(RepertoryFiltersForm::class);
        $entries = $this->entityManager
            ->getRepository(RepertoryEntry::class)
            ->findAll();
        
        return $this->render('repertory/index.html.twig', [
            'filtersForm' => $filtersForm->createView(),
            'entries' => $entries
        ]);
    }
}
