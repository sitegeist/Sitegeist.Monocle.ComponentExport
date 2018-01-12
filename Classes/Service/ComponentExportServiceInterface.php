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
     * Identify the prototypeNames that shall be exported
     *
     * @param array $fusionAst
     * @param string $namespacePrefix
     * @param string $exportDetectionPath
     * @return array
     */
    public function detectExportPrototypes(array $fusionAst, string $namespacePrefix, string $exportDetectionPath);

    /**
     * @param array $fusionAst
     * @param array $exportPrototypes
     * @param string $filename
     */
    public function exportAst(array $fusionAst, array $exportPrototypes, string $filename);

    /**
     * @param array $fusionAst
     * @param array $exportPrototypes
     * @param string $directory
     * @param string $namespace
     * @return mixed
     */
    public function exportWrapper(array $fusionAst, array $exportPrototypes, string $directory, string $namespace);
}
