<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Form\FormErrorIterator;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class FormErrorsFormatter
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }
    
    public function toJsonResponseData(FormErrorIterator $errorIterator): array
    {
        $result = [];
        foreach ($errorIterator as $error) {
            $label = $error
                ->getOrigin()
                ?->getConfig()
                ?->getOption('label');

            $key = $label ? $this->translator->trans($label) : $error->getOrigin()->getName();
            $result[$key] = $error->getMessage();
        }
        
        return $result;
    }
}
