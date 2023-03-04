<?php

namespace App\Service\OptionsProvider;

use App\Entity\Order;

class OrderOptionsProvider implements OptionsProviderInterface
{
    public const ACTION_DELETE = [
        'label' => 'Delete order',
        'icon' => 'delete',
        'class' => 'js-delete-link'
    ];
    public const ACTION_EDIT = [
        'label' => 'Edit order',
        'icon' => 'edit',
        'class' =>'js-edit-link'
    ];
    public const ACTION_CREATE_ENTRY = [
        'label' => 'Repertory Entry',
        'icon' => 'repertory_entry',
        'class' => 'js-repertory-entry-link'
    ];
    public const ACTION_EDIT_ENTRY = [
        'label' => 'Edit entry',
        'icon' => 'edit',
        'class' => 'js-edit-entry-link'
    ];
    public const ACTION_RESTORE = [
        'label' => 'Restore',
        'icon' => 'restore',
        'class' => 'js-restore-link'
    ];
    public const ACTION_SHARE = [
        'label' => 'Copy link',
        'icon' => 'share',
        'class' => 'js-share-link'
    ];
    public const ACTIONS = [
        self::ACTION_DELETE, self::ACTION_EDIT, self::ACTION_CREATE_ENTRY, self::ACTION_RESTORE, self::ACTION_SHARE
    ];

    public function getOptions(object $object): array
    {
        if (!$object instanceof Order) {
            return [];
        }

        if ($object->getDeletedAt()) {
            $result[] = self::ACTION_RESTORE;
            $result[] = self::ACTION_SHARE;
            return $result;
        }

        if (!$object->getSettledAt()) {
            $result[] = self::ACTION_EDIT;
        }

        if ($object->getCertified()) {
            $result[] = self::ACTION_CREATE_ENTRY;

//            if (!$object->getRepertoryEntry()) {
//                $result[] = self::ACTION_CREATE_ENTRY;
//            } else {
//                $result[] = self::ACTION_EDIT_ENTRY;
//            }
        }

        if (!$object->getDeletedAt()) {
            $result[] = self::ACTION_DELETE;
        }

        // needs to be the last
        $result[] = self::ACTION_SHARE;

        return $result;
    }
}
