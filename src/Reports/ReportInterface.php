<?php

namespace App\Reports;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * @return array
     * @throws Exception
     */
    public function getPreview() : array;

    /**
     * @return string
     */
    public function renderForm() : string;
}
