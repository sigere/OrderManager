<?php

namespace App\Form;

use App\Entity\RepertoryEntry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepertoryEntryForm extends AbstractType
{
    public const DEFAULT_OPTIONS = [
        'data_class' => RepertoryEntry::class,
        'allow_extra_fields' => true,
        'attr' => [
            'name' => 'repertory_entry_form',
            'data-url' => '/repertory/entry',
            'data-method' => 'POST'
        ]
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('documentIssuer', null, [
                'label' => 'Organ wydający'
            ])
            ->add('comments', null, [
                'label' => 'Komentarze'
            ])
            ->add('copies', NumberType::class, [
                'label' => 'Dodatkowe kopie',
                'required' => false,
                'empty_data' => '0'
            ])
            ->add('copyPrice', NumberType::class, [
                'label' => 'Cena za kopię',
                'required' => false,
                'empty_data' => '0'
            ])
            ->add('documentDate', DateType::class, [
                'label' => 'Data dokumentu',
                'widget' => 'single_text',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(self::DEFAULT_OPTIONS);
    }
}
