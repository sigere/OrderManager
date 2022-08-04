<?php

namespace App\Reports\PerStaffReport;

use App\Entity\Staff;
use App\Reports\AbstractReportForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;

class PerStaffReportForm extends AbstractReportForm
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    protected function getReportName(): string
    {
        return PerStaffReport::NAME;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addDateIntervalFields($builder);
        $builder
            ->add('staff', EntityType::class, [
                'class' => Staff::class,
                'query_builder' => function () {
                    return $this->entityManager->getRepository(Staff::class)->createQueryBuilder('s')
                        ->andWhere('s.deletedAt is null')
                        ->orderBy('s.id', 'ASC');
                },
                'choice_label' => function ($staff) {
                    return $staff->getFirstName() . ' ' . $staff->getLastName();
                },
            ])
        ;
    }
}
