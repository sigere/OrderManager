<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Staff;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArchivesFiltersForm extends AbstractType
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
            ->add('usuniete', CheckboxType::class, [
                'label' => 'Usunięte',
                'attr' => $preferences['archives']['usuniete'] ? ['checked' => 'checked'] : [],
                'label_attr' => ['class' => 'filter-state-label', 'state' => 'usunięte'],
                'required' => false,
            ])

            // columns
            ->add('adoption', CheckboxType::class, [
                'label' => 'Wprowadzono',
                'attr' => $preferences['archives']['adoption'] ? ['checked' => 'checked'] : [],
                'label_attr' => ['class' => 'filter-columns-label first'],
                'required' => false,
            ])
            ->add('client', CheckboxType::class, [
                'label' => 'Klient',
                'attr' => $preferences['archives']['client'] ? ['checked' => 'checked'] : [],
                'label_attr' => ['class' => 'filter-columns-label'],
                'required' => false,
            ])
            ->add('topic', CheckboxType::class, [
                'label' => 'Temat',
                'attr' => $preferences['archives']['topic'] ? ['checked' => 'checked'] : [],
                'label_attr' => ['class' => 'filter-columns-label'],
                'required' => false,
            ])
            ->add('lang', CheckboxType::class, [
                'label' => 'Język',
                'attr' => $preferences['archives']['lang'] ? ['checked' => 'checked'] : [],
                'label_attr' => ['class' => 'filter-columns-label'],
                'required' => false,
            ])
            ->add('deadline', CheckboxType::class, [
                'label' => 'Termin',
                'attr' => $preferences['archives']['deadline'] ? ['checked' => 'checked'] : [],
                'label_attr' => ['class' => 'filter-columns-label'],
                'required' => false,
            ])
            ->add('staff', CheckboxType::class, [
                'label' => 'Wykonawca',
                'attr' => $preferences['archives']['staff'] ? ['checked' => 'checked'] : [],
                'label_attr' => ['class' => 'filter-columns-label'],
                'required' => false,
            ])
            // client
            ->add('select-client', EntityType::class, [
                'class' => Client::class,
                'label' => 'Klient',
                'help' => 'Docelowy język dokumentu zlecenia',
                'attr' => ['class' => 'form-select filter-client first'],
                'label_attr' => ['style' => 'display: none;'],
                'required' => false,
                'placeholder' => 'Wszyscy klienci',
                'data' => $this->entityManager->
                getRepository(Client::class)->
                findOneBy(['id' => $preferences['archives']['select-client']]),

            ])
            ->add('staff', EntityType::class, [
                'class' => Staff::class,
                'label' => 'Wykonawca',
                'help' => 'Tłumacz przydzielony do zlecenia',
                'attr' => ['class' => 'form-select filter-client first'],
                'label_attr' => ['style' => 'display: none;'],
                'required' => false,
                'placeholder' => 'Wszyscy wykonawcy',
                'data' => $this->entityManager->
                getRepository(Staff::class)->
                findOneBy(['id' => $preferences['archives']['staff']]),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([

        ]);
    }
}
