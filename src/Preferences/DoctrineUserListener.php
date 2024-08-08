<?php

declare(strict_types=1);

namespace App\Preferences;

use App\Entity\User;
use App\Preferences\Event\UserPreferencesUpdatedEvent;
use App\Preferences\Model\Preferences;
use App\Preferences\Service\MapperInterface;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Mapping\PostLoad;
use Doctrine\ORM\Mapping\PreFlush;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: UserPreferencesUpdatedEvent::class, method: 'onUserPreferencesUpdated')]
class DoctrineUserListener
{
    private bool $userUpdated = false;

    public function __construct(
        #[AutowireIterator('app.preferences.mapper')] private readonly iterable $mappers,
    ) {
    }

    public function onUserPreferencesUpdated(): void
    {
        $this->userUpdated = true;
    }

    #[PostLoad]
    public function postLoad(User $user): void
    {
        if (!$user->getPreferences()) {
            $user->setPreferences(new Preferences());
        }

        /** @var MapperInterface $mapper */
        foreach ($this->mappers as $mapper) {
            $mapper->fromArray($user);
        }
    }

    #[PreFlush]
    public function preFlush(User $user, PreFlushEventArgs $args): void
    {
        if (!$this->userUpdated) {
            return;
        }

        $em = $args->getObjectManager();
        $unitOfWork = $em->getUnitOfWork();

        $old = $user->getPreferences();
        $new = clone $old;
        $user->setPreferences($new);
        $unitOfWork->recomputeSingleEntityChangeSet($em->getClassMetadata(User::class), $user);
    }
}
