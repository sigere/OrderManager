<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepertoryFiltersForm extends AbstractType
{
    private array $years;
    public function __construct(private EntityManagerInterface $entityManager)
    {
        $this->years = [];
        try {
            $first = $this->entityManager
                ->getRepository(Order::class)
                ->createQueryBuilder('o')
                ->orderBy('o.deadline', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
            $last = $this->entityManager
                ->getRepository(Order::class)
                ->createQueryBuilder('o')
                ->orderBy('o.deadline', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
            if ($last && $first) {
                $f = intval($first->getDeadline()->format('Y'));
                $l = intval($last->getDeadline()->format('Y'));
                for ($i = $f; $i <= $l; ++$i) {
                    $this->years[(string) $i] = $i;
                }
            }
        } catch (Exception $ex) {
            $now = new \DateTime();
            $this->years = [
                $now->format('Y') => intval($now->format('Y')),
            ];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('month', ChoiceType::class, [
                'label' => 'Miesiąc',
                'choices' => [
                    'Wszystkie' => null,
                    'Styczeń' => 1,
                    'Luty' => 2,
                    'Marzec' => 3,
                    'Kwiecień' => 4,
                    'Maj' => 5,
                    'Czerwiec' => 6,
                    'Lipiec' => 7,
                    'Sierpień' => 8,
                    'Wrzesień' => 9,
                    'Październik' => 10,
                    'Listopad' => 11,
                    'Grudzień' => 12,
                ],
            ])
            ->add('year', ChoiceType::class, [
                'label' => 'Rok',
                'choices' => $this->years,
            ])
            ->add('lang', CheckboxType::class, [
                'label' => 'Język',
                'attr' => $preferences['index']['lang'] ? ['checked' => 'checked'] : [],
                'label_attr' => ['class' => 'filter-columns-label'],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
