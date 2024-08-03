<?php
declare(strict_types=1);

return [
    "staff" => null,
    "client" => null,
    "states" => [
        "accepted",
        "done",
        "sent"
    ],
    "deleted" => false,
    "settled" => false,
    "columns" => [
        "adoption",
        "client",
        "topic",
        "lang",
        "deadline",
        "staff"
    ],
    "date_to" => null,
    "date_from" => null,
    "date_type" => "deadline"
];