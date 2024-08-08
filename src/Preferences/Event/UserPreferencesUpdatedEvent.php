<?php

declare(strict_types=1);

namespace App\Preferences\Event;

use App\Entity\User;
use App\Preferences\Model\Preferences;
use Symfony\Contracts\EventDispatcher\Event;

class UserPreferencesUpdatedEvent extends Event
{
    public function __construct(
        private readonly User $user,
        private readonly Preferences $preferences,
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getPreferences(): Preferences
    {
        return $this->preferences;
    }
}
