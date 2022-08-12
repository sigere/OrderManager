<?php

namespace App\Reports;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        $defaults['attr']['data-report-id'] = $this->getReportName();
        $defaults['attr']['data-method'] = 'GET';
        $defaults['method'] = "GET";

        $resolver->setDefaults($defaults);
    }

    final protected function addDateIntervalFields(
        FormBuilderInterface $builder,
        \DateTime $defaultFrom = null,
        \DateTime $defaultTo = null
    ) {
        if ($defaultFrom === null) {
            $defaultFrom = new \DateTime('first day of last month');
        }

        if ($defaultTo === null) {
            $defaultTo = new \DateTime('last day of last month');
        }

        $builder
            ->add('dateFrom', DateType::class, [
                'widget' => 'single_text',
                'data' => $defaultFrom
            ])
            ->add('dateTo', DateType::class, [
                'widget' => 'single_text',
                'data' => $defaultTo
            ])
            ->add('execute', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary'],
                'priority' => -1
            ])
        ;
    }
}
