<?php
declare(strict_types=1);

namespace App\UserPreferences;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class AbstractPreferences
{
    protected User $user;

    /**
     * @throws Exception
     */
    public function __construct(
        protected EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
    ) {
//        $user = $tokenStorage->getToken()->getUser();
//        if (!$user instanceof User) {
//            throw new Exception(sprintf(
//                'User must be an instance of %s.',
//                User::class
//            ));
//        }
//
//        $this->user = $user;
//
//        $file = __DIR__ . '/../Resources/default_'.$this->getArrayKey().'_preferences.json';
//        $config = $this->user->getPreferences()[$this->getArrayKey()] ?? json_decode(
//            file_get_contents($file), true
//        );
//        $this->decode($config);
    }

    abstract protected function getArrayKey(): string;

    abstract protected function decode(array $config): void;

    public function save(): void
    {
        $preferences = $this->user->getPreferences();
        $preferences[$this->getArrayKey()] = $this->encode();
        $this->user->setPreferences($preferences);
        $this->entityManager->persist($this->user);
        $this->entityManager->flush();
    }

    abstract protected function encode(): array;

    abstract public function applyForm(mixed $data): void;
}
