<?php
namespace Sitegeist\Monocle\ComponentExport\Service;

/**
 * Interface ComponentExportServiceInterface
 * @package Sitegeist\Monocle\ComponentExport\Service
 */
interface ComponentExportServiceInterface
{
    /**
     * @return string the short name of the operation
     */
    public static function getFormat();

    /**
     * @param string $prototypeName
     * @param string $prototypeName
     * @param array $prototypeName
     */
    public static function export($targetDirectory, $namespacePrefix, $prototypes);
}
