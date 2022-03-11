<?php

namespace App\Service;

use App\Service\OptionsProvider\OptionsProviderInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class OptionsProviderFactory extends ServiceLocator
{
    public function getOptions(object $object): array
    {
        try {
            /** @var OptionsProviderInterface $service */
            $service = $this->get(
                $this->fromCamelCase(get_class($object))
            );

            return $service->getOptions($object);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            return [];
        }
    }

    private function fromCamelCase(string $input): string
    {
        $array = explode("\\",$input);
        foreach ($array as $key => $value) {
            preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $value, $matches);
            $ret = $matches[0];
            foreach ($ret as &$match) {
                $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
            }
            $array[$key] = implode('_', $ret);
        }
        return implode(".", $array);
    }
}