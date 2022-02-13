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
        $resolver->setDefaults([
            'data_class' => RepertoryEntry::class
        ]);
    }
}
