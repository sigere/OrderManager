<?php

declare(strict_types=1);

namespace App\Preferences\Model;

class Preferences implements \JsonSerializable
{
    private ?OrderPreferences $orderPreferences = null;

    public function __construct(
        public readonly ?array $_data = null
    ) {
    }

    public function getOrdersPreferences(): ?OrderPreferences
    {
        return $this->orderPreferences;
    }

    public function setOrdersPreferences(?OrderPreferences $orderPreferences): Preferences
    {
        $this->orderPreferences = $orderPreferences;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'ordersPreferences' => $this->orderPreferences->jsonSerialize(),
        ];
    }
}
