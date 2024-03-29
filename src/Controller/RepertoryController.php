<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Order;
use App\Entity\RepertoryEntry;
use App\Form\RepertoryEntryForm;
use App\Form\RepertoryFiltersForm;
use App\Repository\RepertoryEntryRepository;
use App\Service\OptionsProviderFactory;
use App\Service\ResponseFormatter;
use App\Service\UserPreferences\RepertoryPreferences;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/repertory")
 */
class RepertoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RepertoryEntryRepository $entryRepository,
        private RepertoryPreferences $preferences,
        private ResponseFormatter $formatter,
        private OptionsProviderFactory $optionsProviderFactory,
    ) {
    }

    /**
     * @Route("/", methods={"GET"}, name="repertory")
     */
    public function index(Request $request): Response
    {
        $filtersForm = $this->createForm(RepertoryFiltersForm::class);
        $entries = $this->entryRepository->getByRepertoryPreferences($this->preferences);
        $entry = $this->entryRepository->findOneBy(['id' => $request->get('entry')]);
        $options = $entry ? $this->optionsProviderFactory->getOptions($entry) : [];

        return $this->render('repertory/index.html.twig', [
            'filtersForm' => $filtersForm->createView(),
            'entries' => $entries,
            'details' => [
                'entry' => $entry
            ],
            'options' => $options,
            'dataSourceUrl' => '/repertory/entry'
        ]);
    }

    /**
     * @Route("/filters", methods={"POST"}, name="repertory_filters")
     */
    public function filters(Request $request): Response
    {
        $form = $this->createForm(RepertoryFiltersForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->preferences->applyForm($form->getData());

            return new Response(
                $this->formatter->success('Zastosowano filtry.'),
                200
            );
        }

        return new Response(
            $this->formatter->error('Błędne dane.'),
            400
        );
    }

    /**
     * @Route("/entry", methods={"GET"}, name="repertory_entry_get_all")
     */
    public function getEntries(): Response
    {
        $entries = $this->entryRepository->getByRepertoryPreferences($this->preferences);

        return $this->render('repertory/entries_table.html.twig', [
            'entries' => $entries,
            'dataSourceUrl' => '/repertory/entry'
        ]);
    }

    /**
     * @Route("/entry/{id}", methods={"GET"}, name="repertory_entry_get")
     */
    public function getEntry(RepertoryEntry $entry): Response
    {
        $result = [];
        $result['details'] = $this->renderView('repertory/details.html.twig', [
            'entry' => $entry
        ]);

        $options = $this->optionsProviderFactory->getOptions($entry);

        $result['burger'] = $this->renderView('burger.html.twig', [
            'options' => $options
        ]);

        return new JsonResponse($result);
    }

    /**
     * @Route("/entry", methods={"POST"}, name="repertory_entry_post")
     */
    public function create(Request $request): Response
    {
        $id = $request->get('order') ?? $request->get('repertory_entry_form')['order'] ?? null;
        $order = $this->entityManager->getRepository(Order::class)
            ->findOneBy(['id' => $id]);

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
                ['Set-Current-Subject' => 'order/' . $order->getId()]
            );
        } elseif (!$form->isSubmitted()) {
            $form->add('order', HiddenType::class, ['data' => $order->getId()]);
        }

        return $this->render(
            'repertory/entry_form.html.twig',
            ['entryForm' => $form->createView()]
        );
    }

    /**
     * @Route("/entry/{id}", methods={"PUT"}, name="repertory_entry_put")
     */
    public function update(RepertoryEntry $entry, Request $request): Response
    {
        $attr = array_merge(RepertoryEntryForm::DEFAULT_OPTIONS['attr'] ?? [], [
            'data-url' => '/repertory/entry/' . $entry->getId(),
            'data-method' => 'PUT'
        ]);
        $options = array_merge(RepertoryEntryForm::DEFAULT_OPTIONS, [
            'attr' => $attr,
            'method' => 'PUT'
        ]);
        $form = $this->createForm(RepertoryEntryForm::class, $entry, $options);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var RepertoryEntry $entry */
            $entry = $form->getData();

            $this->entityManager->persist($entry);
            $this->entityManager->flush();

            return new Response(
                $this->formatter->success("Zaktualizowano wpis"),
                201,
                ['Set-Current-Subject' => 'order/' . $entry->getOrder()->getId()]
            );
        }

        return $this->render('repertory/entry_form.html.twig', [
            'entryForm' => $form->createView(),
            'update' => true
        ]);
    }
}
