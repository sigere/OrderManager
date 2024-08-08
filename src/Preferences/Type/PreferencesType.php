<?php
declare(strict_types=1);

namespace App\Preferences\Type;

use App\Preferences\Model\Preferences;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class PreferencesType extends JsonType
{
    public const string TYPE = 'preferences';

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Preferences
    {
        if ($value === null) {
            return null;
        }

        $array = json_decode($value, true);
        return new Preferences(
            ['orders' => $array['orders'] ?? null]
        );
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (!$value instanceof Preferences) {
            return null;
        }

        return json_encode(
            ['orders' => $value->getOrdersPreferences()],
            JSON_UNESCAPED_UNICODE
        );
    }

    public function getName(): string
    {
        return self::TYPE;
    }

}