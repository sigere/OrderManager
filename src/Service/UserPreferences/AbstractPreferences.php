<?php

namespace App\Service\UserPreferences;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Security;

abstract class AbstractPreferences
{
    protected $user;

    /**
     * @param EntityManagerInterface $entityManager
     * @param Security $security
     * @param KernelInterface $kernel
     */
    public function __construct(
        protected EntityManagerInterface $entityManager,
        Security $security,
        KernelInterface $kernel
    ) {
        $this->user = $security->getUser();
        $file = $kernel->getProjectDir() . '/config/default_' . $this->getArrayKey() . '_preferences.json';
        $config = $this->user->getPreferences()[$this->getArrayKey()]
            ?? json_decode(
                file_get_contents($file),
                true
            );
        $this->decode($config);
    }

    /**
     * @return void
     */
    public function save(): void
    {
        $preferences = $this->user->getPreferences();
        $preferences[$this->getArrayKey()] = $this->encode();
        $this->user->setPreferences($preferences);
        $this->entityManager->persist($this->user);
        $this->entityManager->flush();
    }

    /**
     * @return string
     */
    abstract protected function getArrayKey(): string;

    /**
     * @return array
     */
    abstract protected function encode(): array;

    /**
     * @param array $config
     * @return void
     */
    abstract protected function decode(array $config): void;

    /**
     * @param mixed $data
     * @return void
     */
    abstract public function applyForm(mixed $data): void;
}
