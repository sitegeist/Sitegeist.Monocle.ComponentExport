<?php
namespace Sitegeist\Monocle\ComponentExport\Service\FusionAstFilter;

use Neos\Error\Messages\Notice;
use Neos\Error\Messages\Result;
use Neos\Utility\Arrays;

class FilterPrototypes extends PrototypeListBasedFilter implements FusionAstFilterInterface
{
    /**
     * @param array $ast
     * @param Result $result
     * @param array $prototypeNames
     * @return array
     */
    protected function processInternal(array $ast, Result &$result, array $prototypeNames = []) : array
    {
        $requiredPrototypeNames = $this->getRequiredPrototypeNames($ast, $prototypeNames);
        $basePrototypeNames = $this->getBasePrototypeNames($ast, $requiredPrototypeNames);
        $prototypesToExport = array_unique(array_merge($prototypeNames, $requiredPrototypeNames, $basePrototypeNames));

        $filteredAst = [
            '__prototypes' => []
        ];
        foreach ($prototypesToExport as $prototypeToExport) {
            $filteredAst['__prototypes'][$prototypeToExport] = Arrays::getValueByPath(
                $ast,
                ['__prototypes' , $prototypeToExport]
            );
        }

        return $filteredAst;
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
        foreach ($requiredPrototypeNames as $requiredPrototypeName) {
            $prototypeChain = Arrays::getValueByPath(
                $fusionAst,
                ["__prototypes" , $requiredPrototypeName, '__prototypeChain']
            );
            if (is_array($prototypeChain)) {
                $basePrototypeNames = array_merge($basePrototypeNames, $prototypeChain);
            }
        }

        return array_unique($basePrototypeNames);
    }
}
