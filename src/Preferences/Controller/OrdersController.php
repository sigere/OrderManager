<?php
declare(strict_types=1);

namespace App\Preferences\Controller;

use App\Entity\User;
use App\Preferences\Event\UserPreferencesUpdatedEvent;
use App\Preferences\Form\OrderFiltersForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrdersController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    #[Route('/preferences/filters/orders', name: 'preferences_filters_orders_put', methods: ['PUT'])]
    public function filtersAction(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $preferences = $user->getPreferences();
        $form = $this->createForm(OrderFiltersForm::class, $preferences->getOrdersPreferences());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $preferences->setOrdersPreferences($data);

            $this->eventDispatcher->dispatch(
                new UserPreferencesUpdatedEvent($user, $preferences)
            );

            $this->em->persist($user);
            $this->em->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'message' => $this->translator->trans(
                        id: 'orders.filters.success',
                        domain: 'apis',
                        locale: 'pl'
                    ),
                ],
                200
            );
        }

        return new JsonResponse(
            [
                'success' => false,
                'message' => $this->translator->trans(
                    id: 'orders.filters.fail',
                    domain: 'apis',
                ),
            ],
            400
        );
    }
}