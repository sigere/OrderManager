<?php

namespace App\Service;

use Exception;
use Symfony\Component\HttpFoundation\Request;

interface ReportInterface
{
    /**
     * @return string
     * @throws Exception
     */
    public function export() : string;

    /**
     * @param Request $request
     */
    public function configure(Request $request) : void;
}