<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddClientForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Pełna nazwa',
            ])
            ->add('alias', TextType::class, [
                'label' => 'Alias',
            ])
            ->add('nip', NumberType::class, [
                'label' => 'NIP (tylko cyfry)',
            ])
            ->add('postCode', TextType::class, [
                'label' => 'Kod Pocztowy',
            ])
            ->add('city', TextType::class, [
                'label' => 'Miasto',
            ])
            ->add('street', TextType::class, [
                'label' => 'Ulica',
            ])
            ->add('country', CountryType::class, [
                'label' => 'Kraj',
                'preferred_choices' => ['PL']
            ])
            ->add('email', null, [
                'label' => 'Adres email',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
