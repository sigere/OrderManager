<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class SettingsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('client', CheckboxType::class,[
                'label' => 'Klient',
            ])
            ->add('topic', CheckboxType::class,[
                'label' => 'Temat',
            ])
            ->add('adoption', CheckboxType::class,[
                'label' => 'Wprowadzono',
            ])
            ->add('lang', CheckboxType::class,[
                'label' => 'Język',
            ])
            ->add('deadline', CheckboxType::class,[
                'label' => 'Termin',
            ])
            ->add('', CheckboxType::class,[
                'label' => 'Klient',
            ])
            ->add('info', CheckboxType::class,[
                'label' => 'Notatki',
            ])
            ->add('certified', CheckboxType::class,[
                'label' => 'Uwierzytelnione',
            ])
            ->add('state', CheckboxType::class,[
                'label' => 'Status',
            ])
            ->add('netto', CheckboxType::class,[
                'label' => 'Netto',
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
