<?php

namespace App\Reports\CertifiedUaPlReport;

use App\Reports\AbstractReportForm;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

class CertifiedUaPlReportForm extends AbstractReportForm
{
    protected function getReportName(): string
    {
        return CertifiedUaPlReport::getName();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateFrom', DateType::class, [
                'widget' => 'single_text',
                'data' => new \DateTime('first day of this month')
            ])
            ->add('dateTo', DateType::class, [
                'widget' => 'single_text',
                'data' => new \DateTime('last day of this month')
            ])
        ;
    }
}
