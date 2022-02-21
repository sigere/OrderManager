<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Order;
use App\Entity\RepertoryEntry;
use App\Form\RepertoryEntryForm;
use App\Form\RepertoryFiltersForm;
use App\Repository\RepertoryEntryRepository;
use App\Service\ResponseFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RepertoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RepertoryEntryRepository $entryRepository,
        private ResponseFormatter $formatter
    ) {
    }

    /**
     * @Route("/repertory", methods={"GET"}, name="repertory")
     */
    public function index(Request $request): Response
    {
        $filtersForm = $this->createForm(RepertoryFiltersForm::class);
        $entries = $this->entityManager
            ->getRepository(RepertoryEntry::class)
            ->findAll();
        $entry = $this->entryRepository->findOneBy(['id' => $request->get('entry')]);
        
        return $this->render('repertory/index.html.twig', [
            'filtersForm' => $filtersForm->createView(),
            'entries' => $entries,
            'details' => ['entry' => $entry]
        ]);
    }

    /**
     * @Route("/repertory/entry", methods={"GET"}, name="repertory_entry_get_all")
     */
    public function getEntries(): Response
    {
        $entries = $this->entityManager
            ->getRepository(RepertoryEntry::class)
            ->findAll();

        return $this->render(
            'repertory/entries_table.twig',
            ['entries' => $entries]
        );
    }

    /**
     * @Route("/repertory/entry/{id}", methods={"GET"}, name="repertory_entry_get")
     */
    public function getEntry(RepertoryEntry $entry): Response
    {
        return $this->render(
            'repertory/details.html.twig',
            ['entry' => $entry]
        );
    }

    /**
     * @Route("/repertory/entry", methods={"POST"}, name="repertory_entry_post")
     */
    public function create(Request $request): Response
    {
        $order = $this->entityManager->getRepository(Order::class)
            ->findOneBy(['id' => $request->get('order')]);

        if (!$order) {
            return new Response($this->formatter->error("Nie znaleziono zlecenia."), 400);
        }

        if (!$order->getCertified()) {
            return new Response($this->formatter->error("To zlecenie nie jest uwierzytelniane."), 406);
        }

        if ($order->getRepertoryEntry()) {
            return new Response($this->formatter->error("To zlecenie ma już wpis."), 406);
        }

        $form = $this->createForm(RepertoryEntryForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order = $this->entityManager->getRepository(Order::class)
                ->findOneBy(['id' => $request->get('order')]);

            if (!($order instanceof Order)) {
                return new Response($this->formatter->error("Nie znaleziono zlecenia o podanym id."), 404);
            }

            /** @var RepertoryEntry $entry */
            $entry = $form->getData();
            $this->entityManager
                ->getRepository(RepertoryEntry::class)
                ->configureEntry($entry, $order);

            $this->entityManager->persist($order);
            $this->entityManager->persist($entry);
            $this->entityManager->flush();
            $this->entityManager->persist(new Log(
                $this->getUser(),
                "Dodano wpis do repertorium id: " . $entry->getId(),
                $order
            ));
            $this->entityManager->flush();

            return new Response(
                $this->formatter->success("Dodano nowy wpis"),
                201,
            );
        }

        return $this->render(
            'repertory/entry_form.html.twig',
            ['entryForm' => $form->createView()]
        );
    }

    /**
     * @Route("/repertory/entry1/{id}", methods={"PUT"}, name="repertory_entry_put")
     */
    public function update(RepertoryEntry $entry, Request $request): Response
    {
        $form = $this->createForm(RepertoryEntryForm::class, $entry, ['method' => "PUT"]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var RepertoryEntry $entry */
            $entry = $form->getData();

            $this->entityManager->persist($entry);
            $this->entityManager->flush();

            return new Response(
                "Zaktualizowano wpis",
                201,
            );
        }

        return $this->render(
            'repertory/entry_form.html.twig',
            ['entryForm' => $form->createView()]
        );
    }
}
