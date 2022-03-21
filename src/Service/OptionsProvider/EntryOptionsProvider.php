<?php

namespace App\Service\OptionsProvider;

class EntryOptionsProvider implements OptionsProviderInterface
{
    public const ACTION_EDIT = [
        'label' => 'Edit entry',
        'icon' => 'edit',
        'class' =>'js-edit-link'
    ];
    public const ACTION_EDIT_ORDER = [
        'label' => 'Edit order',
        'icon' => 'edit',
        'class' => 'js-edit-order-link'
    ];
    public const ACTION_SHARE = [
        'label' => 'Copy link',
        'icon' => 'share',
        'class' => 'js-share-link'
    ];
    public const ACTIONS = [
        self::ACTION_EDIT, self::ACTION_EDIT_ORDER,self::ACTION_SHARE
    ];

    public function getOptions(object $object): array
    {
        return self::ACTIONS;
    }
}