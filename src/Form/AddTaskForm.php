<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddTaskForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('topic', TextType::class, [
                'label' => 'Temat',
                'required' =>true,
            ])
            ->add('info', TextareaType::class, [
                'label' => 'Notatki',
                'required' => false,
                'empty_data' => ''
            ])
            ->add('deadline', DateTimeType::class, [
                'label' => 'Termin',
                'help' => 'Data i godzina ostatecznego terminu',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ])
            ->add('target', EntityType::class, [
                'label' => "Wykonawca",
                'class' => User::class,
                'choice_label' => function ($user) {
                    return $user->getFirstName() . ' ' . $user->getLastName();
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
