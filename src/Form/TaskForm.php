<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskForm extends AbstractType
{
    public const DEFAULT_OPTIONS = [
        'data_class' => Task::class,
        'attr' => [
            'name' => 'add_task_form',
            'data-url' => '/tasks/task',
            'data-method' => 'POST'
        ]
    ];

    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('topic', TextType::class, [
                'required' => true,
            ])
            ->add('info', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
            ])
            ->add('deadline', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ])
            ->add('target', EntityType::class, [
                'label' => 'Assignee',
                'class' => User::class,
                'choice_label' => function ($user) {
                    return $user->getFirstName() . ' ' . $user->getLastName();
                },
                'query_builder' => function () {
                    return $this->userRepository->createQueryBuilder('u')
                        ->andWhere('u.deletedAt is null')
                        ->orderBy('u.id', 'DESC');
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(self::DEFAULT_OPTIONS);
    }
}
