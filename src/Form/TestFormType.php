<?php

namespace App\Form;

use App\Entity\RepertoryEntry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('documentIssuer')
            ->add('comments')
            ->add('copies')
            ->add('number')
            ->add('year')
            ->add('documentDate')
            ->add('copyPrice')
            ->add('createdAt')
            ->add('order')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RepertoryEntry::class,
        ]);
    }
}
