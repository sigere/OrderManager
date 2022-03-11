<?php

namespace App\Service\OptionsProvider;

interface OptionsProviderInterface
{
    public function getOptions(object $object): array;
}