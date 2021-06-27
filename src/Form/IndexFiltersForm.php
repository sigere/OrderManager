<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Staff;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class IndexFiltersForm extends AbstractType
{
    private $security;
    private $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $preferences = $this->security->getUser()->getPreferences();
        $builder
            // states
            ->add('przyjete', CheckboxType::class, [
                'label' => 'Przyjęte',
                'attr' => $preferences['index']['przyjete'] ? ['checked' => 'checked'] : [],
                'label_attr' => ['class' => 'filter-state-label', 'state' => 'przyjęte'],
                'required' => false,
            ])
            ->add('wykonane', CheckboxType::class, [
                'label' => 'Wykonane',
                'attr' => $preferences['index']['wykonane'] ? ['checked' => 'checked'] : [],
                'label_attr' => ['class' => 'filter-state-label', 'state' => 'wykonane'],
                'required' => false,
            ])
            ->add('wyslane', CheckboxType::class, [
                'label' => 'Wysłane',
                'attr' => $preferences['index']['wyslane'] ? ['checked' => 'checked'] : [],
                'label_attr' => ['class' => 'filter-state-label', 'state' => 'wysłane'],
                'required' => false,
            ])

            // columns
            ->add('adoption', CheckboxType::class, [
                'label' => 'Wprowadzono',
                'attr' => $preferences['index']['adoption'] ? ['checked' => 'checked'] : [],
                'label_attr' => ['class' => 'filter-columns-label first'],
                'required' => false,
            ])
            ->add('client', CheckboxType::class, [
                'label' => 'Klient',
                'attr' => $preferences['index']['client'] ? ['checked' => 'checked'] : [],
                'label_attr' => ['class' => 'filter-columns-label'],
                'required' => false,
            ])
            ->add('topic', CheckboxType::class, [
                'label' => 'Temat',
                'attr' => $preferences['index']['topic'] ? ['checked' => 'checked'] : [],
                'label_attr' => ['class' => 'filter-columns-label'],
                'required' => false,
            ])
            ->add('lang', CheckboxType::class, [
                'label' => 'Język',
                'attr' => $preferences['index']['lang'] ? ['checked' => 'checked'] : [],
                'label_attr' => ['class' => 'filter-columns-label'],
                'required' => false,
            ])
            ->add('deadline', CheckboxType::class, [
                'label' => 'Termin',
                'attr' => $preferences['index']['deadline'] ? ['checked' => 'checked'] : [],
                'label_attr' => ['class' => 'filter-columns-label'],
                'required' => false,
            ])
            ->add('staff', CheckboxType::class, [
                'label' => 'Wykonawca',
                'attr' => $preferences['index']['staff'] ? ['checked' => 'checked'] : [],
                'label_attr' => ['class' => 'filter-columns-label'],
                'required' => false,
            ])
            // client
            ->add('select-client', EntityType::class, [
                'class' => Client::class,
                'query_builder' => function () {
                    return $this->entityManager->getRepository(Client::class)->createQueryBuilder('c')
                        ->andWhere('c.deletedAt is null')
                        ->orderBy('c.alias', 'ASC');
                },
                'label' => 'Klient',
                'help' => 'Klient',
                'attr' => ['class' => 'form-select filter-client first'],
                'label_attr' => ['style' => 'display: none;'],
                'required' => false,
                'placeholder' => 'Wszyscy klienci',
                'data' => $this->entityManager->
                getRepository(Client::class)->
                findOneBy(['id' => $preferences['index']['select-client']]),
            ])
            // staff
            ->add('select-staff', EntityType::class, [
                'class' => Staff::class,
                'label' => 'Wykonawca',
                'help' => 'Tłumacz przydzielony do zlecenia',
                'attr' => ['class' => 'form-select filter-client first'],
                'label_attr' => ['style' => 'display: none;'],
                'required' => false,
                'placeholder' => 'Wszyscy wykonawcy',
                'data' => $this->entityManager->
                getRepository(Staff::class)->
                findOneBy(['id' => $preferences['index']['select-staff']]),
            ])
            // date
            ->add('date-type', ChoiceType::class, [
                'choices' => [
                    'Data dodania' => 'adoption',
                    'Termin' => 'deadline',
                ],
                'attr' => ['class' => 'filter-date-type'],
                'expanded' => true,
                'multiple' => false,
                'label' => false,
                'data' => $preferences['index']['date-type'],
                'choice_attr' => [
                    'Termin' => ['style' => 'margin-left: 10px;'], ],
            ])
            ->add('date-from', DateType::class, [
                'label' => 'Data od',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'filter-date-from'],
                'data' => $preferences['index']['date-from'] ?
                    new DateTime($preferences['index']['date-from']['date']) : null,
                'label_attr' => ['style' => 'display: block;'],
            ])
            ->add('date-to', DateType::class, [
                'label' => 'Data do',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'filter-date-to'],
                'data' => $preferences['index']['date-to'] ?
                    new DateTime($preferences['index']['date-to']['date']) : null,
                'label_attr' => ['style' => 'display: block;'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
