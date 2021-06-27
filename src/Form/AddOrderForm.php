<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Lang;
use App\Entity\Order;
use App\Entity\Staff;
use DateTime;
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

class AddOrderForm extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $today = new DateTime();
        $builder
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'query_builder' => function () {
                    return $this->entityManager->getRepository(Client::class)->createQueryBuilder('c')
                        ->andWhere('c.deletedAt is null')
                        ->orderBy('c.alias', 'ASC');
                },
                'help' => 'Zleceniodawca nowego zlecenia',
                'label' => 'Klient',
                'choice_label' => 'alias',
            ])
            ->add('staff', EntityType::class, [
                'class' => Staff::class,
                'help' => 'Osoba relizująca zlecenie',
                'label' => 'Tłumacz',
                'choice_label' => function ($staff) {
                    return $staff->getFirstName().' '.$staff->getLastName();
                },
            ])
            ->add('topic', TextType::class, [
                'help' => 'Temat nowego zlecenia',
                'label' => 'Temat',
                'required' => false,
            ])
            ->add('pages', NumberType::class, [
                'label' => 'Strony',
                'required' => false,
                'help' => 'Liczba stron zaokrąglona do dwóch miejsc po przecinku (0.01)',
                'html5' => true,
                'attr' => ['step' => '0.01'],
                'empty_data' => '0',
            ])
            ->add('price', NumberType::class, [
                'label' => 'Cena',
                'required' => false,
                'help' => 'Cena za stronę zaokrąglona do dwóch miejsc po przecinku (0.01)',
                'html5' => true,
                'attr' => ['step' => '0.01'],
                'empty_data' => '0',
            ])
            ->add('baseLang', EntityType::class, [
                'class' => Lang::class,
                'label' => 'Język z',
                'help' => 'Oryginalny język dokumentu zlecenia',
            ])
            ->add('targetLang', EntityType::class, [
                'class' => Lang::class,
                'label' => 'Język na',
                'help' => 'Docelowy język dokumentu zlecenia',
            ])
            ->add('certified', ChoiceType::class, [
                'label' => 'Uwierzytelniane',
                'help' => 'Czy zlecenie będzie uwierzytelniane/przysięgłe?',
                'choices' => [
                    'Nie' => false,
                    'Tak' => true,
                ],
            ])
            ->add('adoption', DateType::class, [
                'label' => 'Przyjęte',
                'help' => 'Data przyjęcia zlecenia',
                'widget' => 'single_text',
//                'empty_data' => "2021-01-01",  TODO
            ])
            ->add('deadline', DateTimeType::class, [
                'label' => 'Termin',
                'help' => 'Data i godzina ostatecznego terminu',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ])
            ->add('info', TextareaType::class, [
                'label' => 'Notatki',
                'required' => false,
                'help' => 'Dodatkowe informacje o zleceniu',
                'empty_data' => '',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
