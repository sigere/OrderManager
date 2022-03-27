<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceSummaryForm extends AbstractType
{
    private const DEFAULT_OPTIONS = [
        'attr' => [
            'class' => "filters-form",
            'name' => 'invoice_summary_form'
        ]
    ];

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
        $resolver->setDefaults(self::DEFAULT_OPTIONS);
    }
}
