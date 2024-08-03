<?php
declare(strict_types=1);

namespace App\Preferences\Form;

use App\Preferences\Model\OrderColumn;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderColumnsForm extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setDataMapper($this);

        foreach (OrderColumn::cases() as $column) {
            $builder->add($column->value, CheckboxType::class, [
                'label' => ucfirst($column->value),
                'label_attr' => ['class' => 'filter-column-label', 'data-column' => $column->value],
                'required' => false,
                'attr' => ['widget_first' => true],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'attr' => [
                'class' => 'orders_columns_form',
            ],
            'label_attr' => ['style' => 'display:none'],
        ]);
    }

    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        if (null === $viewData) {
            return;
        }

        if (!is_array($viewData)) {
            throw new UnexpectedTypeException($viewData, 'array');
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        foreach (OrderColumn::cases() as $column) {
            $forms[$column->value]->setData(
                in_array($column->value, $viewData) ? true : null,
            );
        }
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $viewData = [];
        foreach ($forms as $form) {
            if ($form->getData() === true) {
                $viewData[] = $form->getName();
            }
        }
    }
}