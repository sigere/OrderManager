<?php
declare(strict_types=1);

namespace App\Form\Validator;

use App\Form\Constraint as AppConstraint;
use ReflectionObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OneOfNotEmpty extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var AppConstraint\OneOfNotEmpty $constraint */
        $obj = new ReflectionObject($value);
        foreach ($constraint->getFields() as $field) {
            if (!empty($obj->getProperty($field)->getValue($value))) {
                return;
            }
        }

        $this->context->addViolation($constraint->getMessage());
    }
}