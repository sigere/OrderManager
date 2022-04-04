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
            ->add('month', MonthChoiceType::class, [
                'data' => $this->preferences->getMonth(),
            ])
            ->add('year', ChoiceType::class, [
                'choices' => $this->years,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(self::DEFAULT_OPTIONS);
    }
}
