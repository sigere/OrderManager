<?php

namespace App\Reports;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractReportForm extends AbstractType
{
    protected function getOptions() : array
    {
        return [];
    }

    abstract protected function getReportName() : string;

    public final function configureOptions(OptionsResolver $resolver)
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
}
