<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Enum\OrderState;
use App\Entity\Lang;
use App\Entity\Order;
use App\Entity\Staff;
use App\Repository\ClientRepository;
use App\Repository\StaffRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderForm extends AbstractType
{
    public const string METHOD = 'POST';
    public const array DEFAULT_OPTIONS = [
        'data_class' => Order::class,
        'attr' => [
            'autocomplete' => 'off',
            'name' => 'order_form',
            'class' => 'order-form',
            'data-url' => '/order',
            'data-method' => self::METHOD,
        ],
        'label_attr' => ['style' => 'display:none'],
    ];

    public function __construct(
        private readonly ClientRepository $clientRepository,
        private readonly StaffRepository $staffRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('state', EnumType::class, [
                'class' => OrderState::class,
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'alias',
                'query_builder' => $this->clientRepository->getQueryBuilderForOrderForm(),
                'required' => true,
            ])
            ->add('staff', EntityType::class, [
                'class' => Staff::class,
                'choice_label' => function (Staff $staff) {
                    return $staff->getFirstName().' '.$staff->getLastName();
                },
                'query_builder' => $this->staffRepository->getQueryBuilderForOrderForm(),
            ])
            ->add('topic', TextType::class, [
                'required' => true,
            ])
            ->add('pages', NumberType::class, [
                'html5' => true,
                'attr' => ['step' => '0.01'],
                'empty_data' => '0',
            ])
            ->add('price', NumberType::class, [
                'html5' => true,
                'attr' => ['step' => '0.01'],
                'empty_data' => '0',
            ])
            ->add('additionalFee', NumberType::class, [
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
                'empty_data' => new \DateTime(),
            ])
            ->add('deadline', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'empty_data' => (new \DateTime())->setTime(23, 59),
            ])
            ->add('info', TextareaType::class, [
                'label' => 'Notes',
                'empty_data' => '',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(self::DEFAULT_OPTIONS);
    }
}
