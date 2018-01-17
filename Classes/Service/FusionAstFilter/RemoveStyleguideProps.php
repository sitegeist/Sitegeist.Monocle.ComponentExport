<?php
namespace Sitegeist\Monocle\ComponentExport\Service\FusionAstFilter;

use Neos\Error\Messages\Notice;
use Neos\Error\Messages\Result;
use Neos\Utility\Arrays;

class RemoveStyleguideProps implements FusionAstFilterInterface
{
    /**
     * @param array $ast
     * @param array $arguments
     * @param Result $result
     * @return array
     */
    public function process(array $ast, Result &$result, array $arguments = []) : array
    {
        foreach ($ast['__prototypes'] as $prototypeName => $prototypeAst) {
            $propConfig = Arrays::getValueByPath(
                $ast,
                ["__prototypes", $prototypeName, '__meta', 'styleguide', 'props']
            );
            if ($propConfig) {
                $ast = Arrays::unsetValueByPath(
                    $ast,
                    ["__prototypes", $prototypeName, '__meta', 'styleguide', 'props']
                );
            }
        }
        return $ast;
    }
}
