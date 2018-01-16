<?php
namespace Sitegeist\Monocle\ComponentExport\Service\AstFilter;

use Neos\Error\Messages\Notice;
use Neos\Error\Messages\Result;
Use Neos\Utility\Arrays;

class FilterPrototypesFilter extends PrototypeListBasedFilter implements AstFilterInterface
{
    /**
     * @param array $ast
     * @param array $arguments
     * @param Result $result
     * @return array
     */
    public function process(array $ast, array $arguments = [], Result &$result) : array
    {
        $prototypeNames = $this->getPrototypeNames($ast, $arguments);
        if (!$prototypeNames) {
            return $ast;
        }

        $requiredPrototypeNames = $this->getRequiredPrototypeNames($ast, $prototypeNames);
        $basePrototypeNames = $this->getBasePrototypeNames($ast, $requiredPrototypeNames);
        $prototypesToExport = array_unique(array_merge($prototypeNames, $requiredPrototypeNames, $basePrototypeNames));

        $filteredAst = [];
        foreach ($prototypesToExport as $prototypeToExport) {
            $filteredAst["__prototypes"][$prototypeToExport] = Arrays::getValueByPath($ast, ["__prototypes" , $prototypeToExport]);
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
        foreach($requiredPrototypeNames as $requiredPrototypeName) {
            $prototypeChain = Arrays::getValueByPath($fusionAst, ["__prototypes" , $requiredPrototypeName, '__prototypeChain']);
            if(is_array($prototypeChain)) {
                $basePrototypeNames = array_merge($basePrototypeNames, $prototypeChain);
            }
        }

        return array_unique($basePrototypeNames);
    }

}
