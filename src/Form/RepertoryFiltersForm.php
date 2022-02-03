<?php

namespace App\Form;

use App\Entity\Lang;
use App\Entity\Order;
use App\Entity\RepertoryEntry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepertoryFiltersForm extends AbstractType
{
    private array $years = [2022];
    private array $languages = [0,1];

    public function __construct(private EntityManagerInterface $entityManager)
    {
//        $this->years = [];
//        $this->languages = [];
//        try {
//            $first = $this->entityManager
//                ->getRepository(RepertoryEntry::class)
//                ->createQueryBuilder('o')
//                ->orderBy('o.deadline', 'ASC')
//                ->setMaxResults(1)
//                ->getQuery()
//                ->getSingleResult();
//            $last = $this->entityManager
//                ->getRepository(RepertoryEntry::class)
//                ->createQueryBuilder('o')
//                ->orderBy('o.deadline', 'DESC')
//                ->setMaxResults(1)
//                ->getQuery()
//                ->getSingleResult();
//            if ($last && $first) {
//                $f = intval($first->getDeadline()->format('Y'));
//                $l = intval($last->getDeadline()->format('Y'));
//                for ($i = $f; $i <= $l; ++$i) {
//                    $this->years[(string) $i] = $i;
//                }
//            }
//        } catch (\Exception $ex) {
//            $now = new \DateTime();
//            $this->years = [
//                $now->format('Y') => intval($now->format('Y')),
//            ];
//        }
//        try {
//            $base = $this->entityManager->getRepository(RepertoryEntry::class)
//                ->createQueryBuilder('o')
//                ->select("IDENTITY(o.baseLang) as lang_id")
//                ->groupBy("lang_id" )
//                ->having("COUNT(o.id) > 0")
//                ->getQuery()
//                ->getResult();
//            $target = $this->entityManager->getRepository(RepertoryEntry::class)
//                ->createQueryBuilder('o')
//                ->select("IDENTITY(o.targetLang) as lang_id")
//                ->groupBy("lang_id")
//                ->having("COUNT(o.id) > 0")
//                ->getQuery()
//                ->getResult();
//
//            foreach (array_merge($target, $base) as $lang) {
//                if (!in_array($lang["lang_id"], $this->languages)) {
//                    $this->languages[] = $lang["lang_id"];
//                }
//            }
//
//        } catch (\Exception $ex) {
//            dump($ex);
//            $this->languages = [];
//        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $queryBuilder = $this->entityManager
            ->getRepository(Lang::class)
            ->createQueryBuilder("o");
        $queryBuilder
            ->where($queryBuilder->expr()->in("o.id", $this->languages));

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
            ->add('lang', EntityType::class, [
                'class' => Lang::class,
                'query_builder' => $queryBuilder,
                'label' => 'Język z',
                'help' => 'Oryginalny język dokumentu zlecenia',
                'label_attr' => ['class' => 'filter-columns-label'],
                'required' => true,
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
