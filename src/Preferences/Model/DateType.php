<?php

declare(strict_types=1);

namespace App\Preferences\Model;

enum DateType: string
{
    case Deadline = 'deadline';
    case Adoption = 'adoption';
}
