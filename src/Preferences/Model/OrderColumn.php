<?php
declare(strict_types=1);

namespace App\Preferences\Model;

enum OrderColumn: string
{
    case Adoption = 'adoption';
    case Client = 'client';
    case Topic = 'topic';
    case Lang = 'lang';
    case Deadline = 'deadline';
    case Staff = 'staff';
}