<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Lang;
use App\Entity\Order;
use App\Entity\Staff;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderForm extends AbstractType
{
    public const DEFAULT_OPTIONS = [
        'data_class' => Order::class,
        'attr' => [
            'name' => 'add_order_form',
            'id' => 'add-form',
            'data-url' => '/order',
            'data-method' => 'POST'
        ]
    ];

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $builder->getData();
        $builder
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'query_builder' => function () {
                    return $this->entityManager->getRepository(Client::class)->createQueryBuilder('c')
                        ->andWhere('c.deletedAt is null')
                        ->orderBy('c.alias', 'ASC');
                },
                'choice_label' => 'alias',
                'required' => true
            ])
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
            ->add('topic', TextType::class, [
                'required' => true,
            ])
            ->add('pages', NumberType::class, [
                'required' => false,
                'html5' => true,
                'attr' => ['step' => '0.01'],
                'empty_data' => '0',
            ])
            ->add('price', NumberType::class, [
                'required' => false,
                'html5' => true,
                'attr' => ['step' => '0.01'],
                'empty_data' => '0',
            ])
            ->add('additionalFee', NumberType::class, [
                'required' => false,
                'html5' => true,
                'attr' => ['step' => '0.01'],
                'empty_data' => '0',
            ])
            ->add('baseLang', EntityType::class, [
                'class' => Lang::class,
                ])
            ->add('targetLang', EntityType::class, [
                'class' => Lang::class,
                ])
            ->add('certified', ChoiceType::class, [
                'choices' => [
                    'No' => false,
                    'Yes' => true,
                ],
            ])
            ->add('adoption', DateType::class, [
                'widget' => 'single_text',
                'data' => $entity?->getAdoption() ?? new \DateTime()
            ])
            ->add('deadline', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'data' => $entity?->getDeadline() ?? (new \DateTime())->setTime(23, 59),
            ])
            ->add('info', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'empty_data' => '',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(self::DEFAULT_OPTIONS);
    }
}
