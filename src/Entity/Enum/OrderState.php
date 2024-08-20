<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum OrderState: string
{
    case Accepted = 'accepted';
    case Done = 'done';
    case Sent = 'sent';
}
