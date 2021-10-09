<?php

namespace App\Service;

use Exception;

interface ReportInterface
{
    /**
     * @return string
     * @throws Exception
     */
    public function export() : string;

    /**
     * @param array $config
     */
    public function configure(array $config) : void;
}