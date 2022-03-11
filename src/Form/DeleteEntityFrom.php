<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteEntityFrom extends AbstractType
{
    public const DEFAULT_OPTIONS = [
        'method' => 'delete',
        'attr' => [
            'method' => null,
            'data-method' => 'DELETE'
        ]
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', HiddenType::class, [
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(self::DEFAULT_OPTIONS);
    }
}
