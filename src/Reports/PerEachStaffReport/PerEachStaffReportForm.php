<?php

namespace App\Reports\PerEachStaffReport;

use App\Reports\AbstractReportForm;
use Symfony\Component\Form\FormBuilderInterface;

class PerEachStaffReportForm extends AbstractReportForm
{
    protected function getReportName(): string
    {
        return PerEachStaffReport::NAME;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addDateIntervalFields($builder);
    }
}
