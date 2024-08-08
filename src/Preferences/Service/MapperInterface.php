<?php

declare(strict_types=1);

namespace App\Preferences\Service;

use App\Entity\User;

interface MapperInterface
{
    public function fromArray(User $user): void;
}
