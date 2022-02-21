<?php

namespace App\Service;

class ResponseFormatter
{
    /**
     * @param string $message
     * @return string
     */
    public function error(string $message): string
    {
        return "<div class='alert alert-danger'>" . $message . "</div>";
    }

    public function success(string $message): string
    {
        return "<div class='alert alert-success'>" . $message . "</div>";
    }

    public function notice(string $message): string
    {
        return "<div class='alert alert-primary'>" . $message . "</div>";
    }
}
