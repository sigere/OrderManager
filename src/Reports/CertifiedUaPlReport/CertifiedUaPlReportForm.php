<?php

namespace App\Reports\CertifiedUaPlReport;

use App\Reports\AbstractReportForm;
use Symfony\Component\Form\FormBuilderInterface;

class CertifiedUaPlReportForm extends AbstractReportForm
{
    protected function getReportName(): string
    {
        return CertifiedUaPlReport::NAME;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addDateIntervalFields($builder);
    }
}
