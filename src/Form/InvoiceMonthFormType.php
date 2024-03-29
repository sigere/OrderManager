<?php

namespace App\Form;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceMonthFormType extends AbstractType
{
    private $entityManager;
    private $years;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $query = $entityManager
            ->getRepository(Order::class)
            ->createQueryBuilder('o');

        $this->years = [];
        try {
            $first = $entityManager
                ->getRepository(Order::class)
                ->createQueryBuilder('o')
                ->orderBy('o.deadline', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
            $last = $entityManager
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
                    $this->years[$i.''] = $i;
                }
            }
        } catch (Exception $ex) {
            $now = new \DateTime();
            $this->years = [
                $now->format('Y') => intval($now->format('Y')),
            ];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
