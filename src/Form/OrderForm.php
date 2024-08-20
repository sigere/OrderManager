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
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderForm extends AbstractType
{
    public const array DEFAULT_OPTIONS = [
        'data_class' => Order::class,
        'attr' => [
            'autocomplete' => 'off',
            'name' => 'order_form',
            'class' => 'order-form',
            'data-url' => '/order',
        ],
        'label_attr' => ['style' => 'display:none'],
        'required' => true,
    ];

    public function __construct(
        private readonly ClientRepository $clientRepository,
        private readonly StaffRepository $staffRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ?Order $entity */
        $entity = $builder->getData();

        $builder
            ->add('state', EnumType::class, [
                'class' => OrderState::class,
                'empty_data' => 'null',
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'alias',
                'query_builder' => $this->clientRepository->getQueryBuilderForOrderForm(),
                'empty_data' => 'null',
            ])
            ->add('staff', EntityType::class, [
                'class' => Staff::class,
                'choice_label' => function (Staff $staff) {
                    return $staff->getFirstName().' '.$staff->getLastName();
                },
                'query_builder' => $this->staffRepository->getQueryBuilderForOrderForm(),
                'empty_data' => 'null',
            ])
            ->add('topic', TextType::class, [
                'empty_data' => '',
            ])
            ->add('pages', NumberType::class, [
                'html5' => true,
                'attr' => ['step' => '0.01'],
                'required' => false,
                'empty_data' => '0',
            ])
            ->add('price', NumberType::class, [
                'html5' => true,
                'attr' => ['step' => '0.01'],
                'required' => false,
                'empty_data' => '0',
            ])
            ->add('additionalFee', NumberType::class, [
                'html5' => true,
                'attr' => ['step' => '0.01'],
                'required' => false,
                'empty_data' => '0',
            ])
            ->add('baseLang', EntityType::class, [
                'class' => Lang::class,
                'empty_data' => 'null',
            ])
            ->add('targetLang', EntityType::class, [
                'class' => Lang::class,
                'empty_data' => 'null',
            ])
            ->add('certified', ChoiceType::class, [
                'choices' => [
                    'No' => false,
                    'Yes' => true,
                ],
                'empty_data' => '0',
            ])
            ->add('adoption', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'data' => $entity?->getAdoption() ?? new \DateTime(),
                'empty_data' => [
                    'date' => (new \DateTime())->format('Y-m-d'),
                    'time' => (new \DateTime())->format('H:i'),
                ],
            ])
            ->add('deadline', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'data' => $entity?->getDeadline() ?? (new \DateTime())->setTime(23, 59),
                'empty_data' => [
                    'date' => (new \DateTime())->format('Y-m-d'),
                    'time' => (new \DateTime())->setTime(23, 59)->format('H:i'),
                ],
            ])
            ->add('info', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'empty_data' => '',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(self::DEFAULT_OPTIONS);
    }
}
