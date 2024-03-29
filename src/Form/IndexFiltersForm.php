<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Order;
use App\Entity\Staff;
use App\Service\UserPreferences\IndexPreferences;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IndexFiltersForm extends AbstractType
{
    public const DEFAULT_OPTIONS = [
        'attr' => [
            'class' => "filters-form",
            'name' => 'index_filters_form',
            'data-url' => '/index/filters',
            'data-method' => 'POST'
        ]
    ];

    public function __construct(
        private IndexPreferences $preferences,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $states = $this->preferences->getStates();
        foreach (Order::STATES as $STATE) {
            $builder
                ->add($STATE, CheckboxType::class, [
                    'label' => ucfirst($STATE),
                    'attr' => in_array($STATE, $states) ? ['checked' => 'checked'] : [],
                    'label_attr' => ['class' => 'filter-state-label', 'state' => $STATE],
                    'required' => false,
                ]);
        }

        $builder->add('deleted', CheckboxType::class, [
            'attr' => $this->preferences->getDeleted() ? ['checked' => 'checked'] : [],
            'label_attr' => ['class' => 'filter-state-label', 'state' => 'deleted'],
            'required' => false,
        ]);

        $builder->add('settled', CheckboxType::class, [
            'attr' => $this->preferences->getSettled() ? ['checked' => 'checked'] : [],
            'label_attr' => ['class' => 'filter-state-label', 'state' => 'settled'],
            'required' => false,
        ]);

        $columns = $this->preferences->getColumns();
        $class = 'filter-columns-label';
        foreach (IndexPreferences::COLUMNS as $key => $COLUMN) {
            $first = array_key_first(IndexPreferences::COLUMNS) == $key;
            $builder
                ->add($COLUMN, CheckboxType::class, [
                    'label' => ucfirst($COLUMN),
                    'attr' => in_array($COLUMN, $columns) ? ['checked' => 'checked'] : [],
                    'label_attr' => ['class' => $first ? $class . ' first' : $class],
                    'required' => false,
                ]);
        }
        $builder
            ->add('select-client', EntityType::class, [
                'class' => Client::class,
                'query_builder' => $this->entityManager
                    ->getRepository(Client::class)
                    ->createQueryBuilder('c')
                    ->andWhere('c.deletedAt is null')
                    ->orderBy('c.alias', 'ASC'),
                'label' => 'Client',
                'attr' => ['class' => 'form-select'],
                'label_attr' => ['style' => 'display: none;'],
                'required' => false,
                'placeholder' => 'All clients',
                'data' => $this->preferences->getClient()
            ])
            ->add('select-staff', EntityType::class, [
                'class' => Staff::class,
                'label' => 'Staff',
//                'attr' => ['class' => 'form-select'],
                'label_attr' => ['style' => 'display: none;'],
                'required' => false,
                'placeholder' => 'All staff',
                'query_builder' => $this->entityManager
                    ->getRepository(Staff::class)
                    ->createQueryBuilder('s')
                    ->andWhere('s.deletedAt is null')
                    ->orderBy('s.lastName', 'ASC'),
                'data' => $this->preferences->getStaff()
            ])
            ->add('date-type', ChoiceType::class, [
                'choices' => [
                    'Adoption' => IndexPreferences::DATE_TYPE_ADOPTION,
                    'Deadline' => IndexPreferences::DATE_TYPE_DEADLINE,
                ],
                'attr' => ['class' => 'filter-date-type'],
                'expanded' => true,
                'multiple' => false,
                'label' => false,
                'data' => $this->preferences->getDateType(),
            ])
            ->add('date-from', DateType::class, [
                'label' => 'Date from',
                'widget' => 'single_text',
                'required' => false,
                'data' => $this->preferences->getDateFrom(),
                'label_attr' => ['style' => 'display: block;'],
            ])
            ->add('date-to', DateType::class, [
                'label' => 'Date to',
                'widget' => 'single_text',
                'required' => false,
                'data' => $this->preferences->getDateTo(),
                'label_attr' => ['style' => 'display: block;'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(self::DEFAULT_OPTIONS);
    }
}
