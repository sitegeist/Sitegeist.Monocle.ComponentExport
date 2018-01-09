<?php
namespace Sitegeist\Monocle\ComponentExport\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Utility\Arrays;

/**
 * Class AbstractComponentExportService
 * @package Sitegeist\Monocle\ComponentExport\Service
 */
abstract class AbstractComponentExportService implements ComponentExportServiceInterface
{

    /**
     * @param array $fusionAst
     * @param string $filename
     */
    public function export(array $fusionAst, string $filename)
    {
        $exportPrototypeNames = $this->getExportPrototypeNamesFromAst($fusionAst);

        $requiredPrototypeNames = $this->getRequiredPrototypeNames($fusionAst, $exportPrototypeNames);

        $basePrototypeNames = $this->getBasePrototypeNames($fusionAst, $requiredPrototypeNames);

        $prototypes = array_unique(array_merge($exportPrototypeNames, $requiredPrototypeNames, $basePrototypeNames));

        $prototypeAst = $this->reduceFusionAstToRequiredPrototypeNames($fusionAst, $prototypes);

        $prototypeAst = $this->postProcessAst($prototypeAst, $exportPrototypeNames);

        file_put_contents($filename, json_encode($prototypeAst, JSON_PRETTY_PRINT));
    }

    /**
     * Replace the implementations of the base fusion objects
     *
     * @param array $fusionAst
     * @param array $exportPrototypeNames
     * @return array
     */
    abstract protected function postProcessAst(array $fusionAst, array $exportPrototypeNames);

    /**
     * Get the fusion-path that is used to identify the prototypes that shall be exported
     *
     * @return string
     */
    abstract protected function getExportPrototypeDetectionPath();

    /**
     * Identify the prototypeNames that shall be exported
     *
     * @param array $fusionAst
     * @return array
     */
    protected function getExportPrototypeNamesFromAst($fusionAst)
    {
        $exportPrototypeDetectionPath = $this->getExportPrototypeDetectionPath();
        $exportPrototypeNames = [];
        if ($fusionAst && $fusionAst['__prototypes']) {
            foreach ($fusionAst['__prototypes'] as $prototypeFullName => $prototypeObject) {
                $enableExportConfiguration = Arrays::getValueByPath($prototypeObject, $exportPrototypeDetectionPath);
                if ($enableExportConfiguration) {
                    $exportPrototypeNames[] = $prototypeFullName;
                }
            }
        }
        return $exportPrototypeNames;
    }

    /**
     * Identifies the internally used prototypes that are needed to render the given list of prototypes
     *
     * @param $fusionAst
     * @param $exportPrototypeNames
     */
    protected function getRequiredPrototypeNames($fusionAst, $exportPrototypeNames)
    {
        $requiredPrototypeNames = [];
        foreach ($exportPrototypeNames as $exportPrototypeName) {
            if ($prototypeAst = Arrays::getValueByPath($fusionAst, ["__prototypes", $exportPrototypeName])) {
                $usedPrototypes = [];
                $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($prototypeAst));
                foreach ($iterator as $key => $value) {
                    if ($key === '__objectType' && $value) {
                        $usedPrototypes[] = $value;
                    }
                }

                if ($usedPrototypes) {
                    $subRequirements = $this->getRequiredPrototypeNames($fusionAst, $usedPrototypes);
                    $requiredPrototypeNames = array_merge($requiredPrototypeNames, $usedPrototypes, $subRequirements);
                }
            }
        }

        return array_unique($requiredPrototypeNames);
    }

    /**
     * Identifies the base prototypes that are needed to render the given list of prototypes
     *
     * @param $fusionAst
     * @param $exportPrototypeNames
     */
    protected function getBasePrototypeNames($fusionAst, $requiredPrototypeNames)
    {
        $basePrototypeNames = [];

        // check inheritance chain and add all prototypes found
        foreach($requiredPrototypeNames as $requiredPrototypeName) {
            $prototypeChain = Arrays::getValueByPath($fusionAst, ["__prototypes" , $requiredPrototypeName, '__prototypeChain']);
            if(is_array($prototypeChain)) {
                $basePrototypeNames = array_merge($basePrototypeNames, $prototypeChain);
            }
        }

        return array_unique($basePrototypeNames);
    }

    /**
     * @param array $fusionAst
     * @param arraay $requiredPrototypeNames
     * @return array
     */
    protected function reduceFusionAstToRequiredPrototypeNames($fusionAst, $requiredPrototypeNames)
    {
        // build result
        $result = [];
        foreach ($requiredPrototypeNames as $requiredPrototypeName) {
            $result["__prototypes"][$requiredPrototypeName] = Arrays::getValueByPath($fusionAst, ["__prototypes" , $requiredPrototypeName]);
        }

        return $result;
    }

    /**
     * @param $fusionAstExcerpt
     * @return array
     */
    protected function extractPrototypeNamesFromAstExcerpt($fusionAstExcerpt)
    {

    }

}
