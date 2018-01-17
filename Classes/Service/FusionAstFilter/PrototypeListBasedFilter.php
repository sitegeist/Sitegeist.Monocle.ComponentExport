<?php
namespace Sitegeist\Monocle\ComponentExport\Service\FusionAstFilter;

use Neos\Error\Messages\Result;
use Neos\Utility\Arrays;

abstract class PrototypeListBasedFilter implements FusionAstFilterInterface
{

    /**
     * @param array $ast
     * @param Result $result
     * @param array $arguments
     * @return array
     */
    public function process(array $ast, Result &$result, array $arguments = []) : array
    {
        $prototypeNames = $this->getPrototypeNames($ast, $arguments);
        return $this->processInternal($ast, $result, $prototypeNames);
    }

    /**
     * @param array $ast
     * @param Result $result
     * @param array $prototypeNames
     * @return array
     */
    abstract protected function processInternal(array $ast, Result &$result, array $prototypeNames = []) : array;

    /**
     * Create a list of protoype names
     *
     * @param array $ast
     * @param array $arguments
     */
    protected function getPrototypeNames(array $ast, array $arguments = [])
    {
        $prototypeNames = array_keys(Arrays::getValueByPath($ast, ['__prototypes']));

        $localFusionPath = Arrays::getValueByPath($arguments, 'path');
        $vendorPattern = Arrays::getValueByPath($arguments, 'vendor');
        $namePattern = Arrays::getValueByPath($arguments, 'name');

        $result = [];
        foreach ($prototypeNames as $prototypeFullName) {
            list($vendorName, $prototypeName) = explode(':', $prototypeFullName, 2);

            $includeInResult = true;

            //
            // check arguments.path
            //
            if ($localFusionPath) {
                $metaValue = Arrays::getValueByPath(
                    $ast,
                    array_merge(['__prototypes' , $prototypeFullName], explode('.', $localFusionPath))
                );
                if (!$metaValue) {
                    $includeInResult = false;
                }
            }

            //
            // check arguments.vendor and arguments.name
            //
            if ($vendorPattern && fnmatch($vendorPattern, $vendorName) === false) {
                $includeInResult = false;
            }
            if ($namePattern && fnmatch($namePattern, $prototypeName) === false) {
                $includeInResult = false;
            }

            //
            // Only include results that match all required conditions
            //
            if ($includeInResult) {
                $result[] = $prototypeFullName;
            }
        }

        return $result;
    }
}
