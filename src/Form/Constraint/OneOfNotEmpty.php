<?php
declare(strict_types=1);

namespace App\Form\Constraint;

use App\Form\Validator as AppValidator;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class OneOfNotEmpty extends Constraint
{
    /**
     * @var string[] $fields
     */
    public function __construct(
        private readonly array $fields,
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct([], $groups, $payload);
    }

    public function getTargets(): string|array
    {
        return Constraint::CLASS_CONSTRAINT;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function validatedBy(): string
    {
        return AppValidator\OneOfNotEmpty::class;
    }

    public function getMessage(): string
    {
        return sprintf(
            'One of fields: %s must not be empty.',
            implode(', ', $this->fields)
        );
    }
}