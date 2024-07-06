<?php

namespace App\Form;

use App\Entity\Staff;
use App\Repository\RepertoryEntryRepository;
use App\Service\UserPreferences\RepertoryPreferences;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepertoryFiltersForm extends AbstractType
{
    public const DEFAULT_OPTIONS = [
        'attr' => [
            'class' => "filters-form",
            'name' => 'repertory_filters_form',
            'data-url' => '/repertory/filters',
            'data-method' => 'POST'
        ]
    ];

    public function __construct(
        private RepertoryPreferences $preferences,
        private EntityManagerInterface $entityManager,
        private RepertoryEntryRepository $entryRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('year', ChoiceType::class, [
                'placeholder' => 'All years',
                'choices' => $this->entryRepository->getYearsUsed(),
                'attr' => ['class' => 'form-select'],
                'data' => $this->preferences->getYear(),
                'required' => false
            ])
            ->add('month', MonthChoiceType::class, [
                'placeholder' => 'All months',
                'attr' => ['class' => 'form-select'],
                'data' => $this->preferences->getMonth(),
                'required' => false
            ])
            ->add('select-staff', EntityType::class, [
                'class' => Staff::class,
                'label' => 'Staff',
                'attr' => ['class' => 'form-select'],
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(self::DEFAULT_OPTIONS);
    }
}
