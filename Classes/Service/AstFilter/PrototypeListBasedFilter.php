<?php
namespace Sitegeist\Monocle\ComponentExport\Service\AstFilter;

use Neos\Error\Messages\Result;
use Neos\Utility\Arrays;

abstract class PrototypeListBasedFilter implements AstFilterInterface
{

    /**
     * Create a list of protoype names
     *
     * @param array $ast
     * @param array $arguments
     */
    protected function getPrototypeNames(array $ast, array $arguments = []) {
        $prototypeNames = array_keys(Arrays::getValueByPath($ast, ['__prototypes']));

        $localFusionPath = Arrays::getValueByPath($arguments, 'localFusionPath');
        $fusionNamespacePrefix = Arrays::getValueByPath($arguments, 'fusionNamespacePrefix');
        $fusionNames = Arrays::getValueByPath($arguments, 'fusionNames');

        $result = [];
        foreach ($prototypeNames as $prototypeName) {
            $includeInResult = true;

            //
            // limit by arguments.localFusionPath
            //
            if ($localFusionPath) {
                $metaValue = Arrays::getValueByPath($ast, array_merge(['__prototypes' , $prototypeName], explode('.', $localFusionPath)));
                if (!$metaValue) {
                    $includeInResult = false;
                }
            }

            //
            // limit by arguments.fusionNamespacePrefix
            //
            if ($fusionNamespacePrefix && (strpos($prototypeName, $fusionNamespacePrefix) !== 0) ) {
                $includeInResult = false;
            }

            //
            // limit by arguments.fusionNames
            //
            if ($fusionNames && is_array($fusionNames) && !in_array($prototypeName, $fusionNames)) {
                $includeInResult = false;
            }

            if ($includeInResult) {
                $result[] = $prototypeName;
            }
        }

        return $result;
    }


}
