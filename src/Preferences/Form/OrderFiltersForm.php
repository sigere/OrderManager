<?php
declare(strict_types=1);

namespace App\Preferences\Form;

use App\Entity\Client;
use App\Entity\Staff;
use App\Entity\User;
use App\Preferences\Model\DateType as AppDateType;
use App\Preferences\Model\OrderPreferences;
use App\Repository\ClientRepository;
use App\Repository\StaffRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class OrderFiltersForm extends AbstractType
{
    public const string METHOD = 'PUT';
    public const array DEFAULT_OPTIONS = [
        'data_class' => OrderPreferences::class,
        'attr' => [
            'class' => "filters-form",
            'name' => 'order_filters_form',
            'data-method' => self::METHOD,
            'autocomplete' => "off",
        ],
        'label_attr' => ['style' => 'display:none'],
    ];

    public function __construct(
        private readonly RouterInterface $router,
        private readonly Security $security,
        private readonly ClientRepository $clientRepository,
        private readonly StaffRepository $staffRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $preferences = $user->getPreferences()->getOrdersPreferences();

        $builder
            ->setAction($this->router->generate('preferences_filters_orders_put'))
            ->setMethod(self::METHOD);

        $builder->add('states', OrderStatesForm::class, [
            'data' => $preferences->getStates(),
        ]);

        $builder
            ->add('deleted', CheckboxType::class, [
                'attr' => ['widget_first' => true],
                'required' => false,
            ])
            ->add('settled', CheckboxType::class, [
                'attr' => ['widget_first' => true],
                'required' => false,
            ]);

        $builder->add('columns', OrderColumnsForm::class, [
            'data' => $preferences->getColumns(),
        ]);

        $builder
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'query_builder' => $this->clientRepository->getQueryBuilderForOrdersFiltersForm(),
                'required' => false,
                'placeholder' => 'All clients',
            ])
            ->add('staff', EntityType::class, [
                'class' => Staff::class,
                'query_builder' => $this->staffRepository->getQueryBuilderForOrdersFiltersForm(),
                'required' => false,
                'placeholder' => 'All staff',
            ])
            ->add('dateType', EnumType::class,[
                'class' => AppDateType::class
            ])
            ->add('dateFrom', DateType::class, [
                'label' => 'Date from',
                'widget' => 'single_text',
                'required' => false,
                'data' => $preferences->getDateFrom(),
            ])
            ->add('dateTo', DateType::class, [
                'label' => 'Date to',
                'widget' => 'single_text',
                'required' => false,
                'data' => $preferences->getDateTo(),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(self::DEFAULT_OPTIONS);
    }
}
