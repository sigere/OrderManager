<?php

namespace App\Reports;

use Exception;
use Symfony\Component\HttpFoundation\Request;

interface ReportInterface
{
    /**
     * @return string
     */
    public static function getName() : string;

    /**
     * @return string
     */
    public static function getNameForUI() : string;

    /**
     * @return string
     * @throws Exception
     */
    public function export() : string;

    /**
     * @param mixed $data
     * @throws MissingParameterException
     */
    public function configure(mixed $data) : void;

    /**
     * @return array
     * @throws Exception
     */
    public function getData() : array;

    /**
     * @return string
     */
    public function getFormFQCN() : string;
}
