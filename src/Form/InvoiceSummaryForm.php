<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceSummaryForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $company = $builder->getData();
        $builder
            ->add('issueDate', DateType::class, [
                'label' => 'Data wystawienia',
                'widget' => 'single_text',
                'attr' => ['class' => 'date'],
                'data' => $company ? $company->getIssueDate() : null,
                'label_attr' => ['style' => 'display: block;'],
            ])
            ->add('paymentTo', DateType::class, [
                'label' => 'Płatność do',
                'widget' => 'single_text',
                'attr' => ['class' => 'date'],
                'data' => $company ? $company->getPaymentTo() : null,
                'label_attr' => ['style' => 'display: block;'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
