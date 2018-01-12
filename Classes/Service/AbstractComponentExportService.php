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
     * Identify the prototypeNames that shall be exported
     *
     * @param array $fusionAst
     * @param string $namespacePrefix
     * @param string $exportDetectionPath
     * @return array
     */
    public function detectExportPrototypes(array $fusionAst, string $namespacePrefix = null, string $exportEnablePath = null)
    {
        $exportPrototypeNames = [];
        if ($fusionAst && $fusionAst['__prototypes']) {
            foreach ($fusionAst['__prototypes'] as $prototypeFullName => $prototypeObject) {
                $include = true;
                if ($namespacePrefix) {
                    if (strpos($prototypeFullName, $namespacePrefix) !== 0) {
                        $include = false;
                    }
                }
                if ($exportEnablePath) {
                    $exportIsEnabled = Arrays::getValueByPath($prototypeObject, $exportEnablePath);
                    if (!$exportIsEnabled) {
                        $include = false;
                    }
                }

                if ($include) {
                    $exportPrototypeNames[] = $prototypeFullName;
                }
            }
        }
        return $exportPrototypeNames;
    }

    /**
     * Export the fusion ast as json to the given file
     *
     * @param array $fusionAst
     * @param array $exportPrototypes
     * @param string $filename
     */
    public function exportAst(array $fusionAst, array $exportPrototypes, string $filename)
    {
        $fusionAst = $this->removeStyleguideMetaProperties($fusionAst);

        $requiredPrototypeNames = $this->getRequiredPrototypeNames($fusionAst, $exportPrototypes);

        $basePrototypeNames = $this->getBasePrototypeNames($fusionAst, $requiredPrototypeNames);

        $prototypes = array_unique(array_merge($exportPrototypes, $requiredPrototypeNames, $basePrototypeNames));

        $prototypeAst = $this->reduceFusionAstToRequiredPrototypeNames($fusionAst, $prototypes);

        $prototypeAst = $this->postProcessAst($prototypeAst, $exportPrototypes);

        file_put_contents($filename, json_encode($prototypeAst, JSON_PRETTY_PRINT));
    }

    /**
     * Replace the implementations of the base fusion objects
     *
     * @param array $fusionAst
     * @param array $exportPrototypeNames
     * @return array
     */
    protected function postProcessAst(array $fusionAst, array $exportPrototypeNames) {
        return $fusionAst;
    }

    /**
     * Remove the styleguide props and propSets, since those contain are not needed ob the target platform
     * and use fusion components that can only work inside neos
     *
     * @param array $fusionAst
     * @return array
     */
    protected function removeStyleguideMetaProperties(array $fusionAst) {
        foreach ($fusionAst['__prototypes'] as $prototypeName => $prototypeAst) {
            $propConfig = Arrays::getValueByPath($fusionAst, ["__prototypes", $prototypeName, '__meta', 'styleguide', 'props']);
            if($propConfig) {
                $fusionAst = Arrays::unsetValueByPath($fusionAst, ["__prototypes", $prototypeName, '__meta', 'styleguide', 'props']);
            }
        }
        return $fusionAst;
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
     * @param array $requiredPrototypeNames
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


}
