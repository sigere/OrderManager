<?php

namespace App\Service\OptionsProvider;

class ClientOptionsProvider implements OptionsProviderInterface
{
    public const ACTION_EDIT = [
        'label' => 'Edit Client',
        'icon' => 'edit',
        'class' =>'js-edit-link'
    ];
    public const ACTION_SHARE = [
        'label' => 'Copy link',
        'icon' => 'share',
        'class' => 'js-share-link'
    ];
    public const ACTIONS = [
        self::ACTION_EDIT, self::ACTION_SHARE
    ];

    public function getOptions(object $object): array
    {
        return self::ACTIONS;
    }
}