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
    public function getFormat();

    /**
     * @param array $fusionAst
     * @param string $filename
     */
    public function export(array $fusionAst, string $filename);
}
