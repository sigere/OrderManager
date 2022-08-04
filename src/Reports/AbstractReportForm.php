<?php

namespace App\Reports;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractReportForm extends AbstractType
{
    protected function getOptions() : array
    {
        return [];
    }

    abstract protected function getReportName() : string;

    final public function configureOptions(OptionsResolver $resolver)
    {
        $defaults = $this->getOptions();
        if (!(isset($defaults['attr']) && is_array($defaults['attr']))) {
            $defaults['attr'] = [];
        }

        $defaults['attr']['data-url'] = '/reports/execute/' . $this->getReportName();
        $defaults['attr']['data-method'] = 'GET';
        $defaults['method'] = "GET";

        $resolver->setDefaults($defaults);
    }

    final protected function addDateIntervalFields(FormBuilderInterface $builder)
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
