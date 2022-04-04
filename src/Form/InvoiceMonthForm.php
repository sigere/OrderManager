<?php

namespace App\Form;

use App\Entity\Order;
use App\Service\UserPreferences\InvoicesPreferences;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceMonthForm extends AbstractType
{
    private const DEFAULT_OPTIONS = [
        'attr' => [
            'class' => "filters-form",
            'name' => 'invoice_month_form'
        ]
    ];
    private array $years;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private InvoicesPreferences $preferences
    ) {
        $this->years = [];
        try {
            $first = $this->entityManager
                ->getRepository(Order::class)
                ->createQueryBuilder('o')
                ->orderBy('o.deadline', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
            $last = $this->entityManager
                ->getRepository(Order::class)
                ->createQueryBuilder('o')
                ->orderBy('o.deadline', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
            if ($last && $first) {
                $f = intval($first->getDeadline()->format('Y'));
                $l = intval($last->getDeadline()->format('Y'));
                for ($i = $f; $i <= $l; ++$i) {
                    $this->years[(string) $i] = $i;
                }
            }
        } catch (Exception $ex) {
            $now = new \DateTime();
            $this->years = [
                $now->format('Y') => intval($now->format('Y')),
            ];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('month', ChoiceType::class, [
                'label' => 'Miesiąc',
                'data' => $this->preferences->getMonth(),
                'choices' => [
                    'Wszystkie' => null,
                    'Styczeń' => 1,
                    'Luty' => 2,
                    'Marzec' => 3,
                    'Kwiecień' => 4,
                    'Maj' => 5,
                    'Czerwiec' => 6,
                    'Lipiec' => 7,
                    'Sierpień' => 8,
                    'Wrzesień' => 9,
                    'Październik' => 10,
                    'Listopad' => 11,
                    'Grudzień' => 12,
                ],
            ])
            ->add('year', ChoiceType::class, [
                'label' => 'Rok',
                'choices' => $this->years,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(self::DEFAULT_OPTIONS);
    }
}
