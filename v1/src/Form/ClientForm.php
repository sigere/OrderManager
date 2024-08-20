<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientForm extends AbstractType
{
    public const DEFAULT_OPTIONS = [
        'data_class' => Client::class,
        'attr' => [
            'name' => 'add_client_form',
            'id' => 'add-form',
            'data-url' => '/clients/client',
            'data-method' => 'POST'
        ]
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Full name',
            ])
            ->add('alias', TextType::class, [
            ])
            ->add('nip', NumberType::class, [
                'label' => 'NIP (digits only)',
            ])
            ->add('postCode', TextType::class, [
            ])
            ->add('city', TextType::class, [
            ])
            ->add('street', TextType::class, [
            ])
            ->add('country', CountryType::class, [
                'preferred_choices' => ['PL']
            ])
            ->add('email', null, [
                'label' => 'Email address',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(self::DEFAULT_OPTIONS);
    }
}
