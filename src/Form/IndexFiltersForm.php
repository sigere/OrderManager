<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Security\Core\Security;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class IndexFiltersForm extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
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
            ->add('rozliczone', CheckboxType::class, [
                'label' => 'Rozliczone',
                'attr' => $preferences['index']['rozliczone'] ? ['checked' => 'checked'] : [],
                'label_attr' => ['class' => 'filter-state-label', 'state' => 'rozliczone'],
                'required' => false,
            ])
            ->add('usuniete', CheckboxType::class, [
                'label' => 'Usunięte',
                'attr' => $preferences['index']['usuniete'] ? ['checked' => 'checked'] : [],
                'label_attr' => ['class' => 'filter-state-label', 'state' => 'usunięte'],
                'required' => false,
            ])
            
            // columns
            ->add('adoption', CheckboxType::class, [
                'label' => 'Wprowadzono',
                'attr' =>  $preferences['index']['adoption'] ? ['checked' => 'checked'] : [],
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
                'label' => 'Klient',
                'help' => 'Docelowy język dokumentu zlecenia',
                'attr' => ['class' => 'form-select filter-client first'],
                'label_attr' => ['style' => 'display: none;'],
                'required' => false,
                'placeholder' => 'Wszyscy klienci',
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
