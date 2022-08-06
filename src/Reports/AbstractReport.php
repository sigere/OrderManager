<?php

namespace App\Reports;

use App\Reports\Exception\ExportException;
use App\Reports\Exception\MissingParameterException;
use Exception;
use Shuchkin\SimpleXLSXGen;

abstract class AbstractReport
{
    public const GENERATED_FILES_PATH = "/var/www/OrderManager/var/tmp";
    protected array $config;

    /**
     * @return string
     */
    abstract public function getName() : string;

    /**
     * @return string
     */
    abstract public function getNameForUI() : string;

    /**
     * @return string
     * @throws Exception
     */
    final public function exportToXLSX() : string
    {
        if (!isset($this->config)) {
            throw new Exception('Report not configured.');
        }

        $array = $this->getData();

        $filename = $this->getName() . '-' . date("YmdHis") . '.xlsx';
        $path = self::GENERATED_FILES_PATH . '/' . $filename;
        if (!file_exists(self::GENERATED_FILES_PATH)) {
            if (!mkdir(self::GENERATED_FILES_PATH, 0775)) {
                throw new ExportException("Could not create directory on filesystem.");
            }
        }

        SimpleXLSXGen::fromArray($array)->saveAs($path);
        return $filename;
    }

    /**
     * @param mixed $data
     * @throws MissingParameterException
     */
    abstract public function configure(mixed $data) : void;

    /**
     * @return array
     * @throws Exception
     */
    abstract public function getData() : array;

    /**
     * @return string
     */
    abstract public function getFormFQCN() : string;
}
