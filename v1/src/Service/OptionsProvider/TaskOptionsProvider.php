<?php

namespace App\Service\OptionsProvider;

use App\Entity\Task;

class TaskOptionsProvider implements OptionsProviderInterface
{
    public const ACTION_DELETE = [
        'label' => 'Delete task',
        'icon' => 'delete',
        'class' => 'js-delete-link'
    ];
    public const ACTION_EDIT = [
        'label' => 'Edit task',
        'icon' => 'edit',
        'class' =>'js-edit-link'
    ];
    public const ACTION_SHARE = [
        'label' => 'Copy link',
        'icon' => 'share',
        'class' => 'js-share-link'
    ];
    public const ACTIONS = [
        self::ACTION_DELETE, self::ACTION_EDIT, self::ACTION_SHARE
    ];

    public function getOptions(object $object): array
    {
        if (!$object instanceof Task) {
            return [];
        }

        if ($object->getDeletedAt()) {
            $result[] = self::ACTION_SHARE;
            return $result;
        }

        $result[] = self::ACTION_EDIT;
        $result[] = self::ACTION_DELETE;

        $result[] = self::ACTION_SHARE;
        return $result;
    }
}
