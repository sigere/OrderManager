<?php

declare(strict_types=1);

namespace App\Preferences\Form;

use App\Entity\Enum\OrderState;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderStatesForm extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setDataMapper($this);

        foreach (OrderState::cases() as $state) {
            $builder->add($state->value, CheckboxType::class, [
                'label' => ucfirst($state->value),
                'label_attr' => ['class' => 'filter-state-label', 'data-state' => $state->value],
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
                'class' => 'orders_states_form',
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

        foreach (OrderState::cases() as $state) {
            $forms[$state->value]->setData(
                in_array($state->value, $viewData) ? true : null,
            );
        }
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $viewData = [];
        foreach ($forms as $form) {
            if (true === $form->getData()) {
                $viewData[] = $form->getName();
            }
        }
    }
}
