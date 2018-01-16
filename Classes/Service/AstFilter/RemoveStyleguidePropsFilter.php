<?php
namespace Sitegeist\Monocle\ComponentExport\Service\AstFilter;

use Neos\Error\Messages\Notice;
use Neos\Error\Messages\Result;
Use Neos\Utility\Arrays;

class RemoveStyleguidePropsFilter implements AstFilterInterface
{
    /**
     * @param array $ast
     * @param array $arguments
     * @param Result $result
     * @return array
     */
    public function process(array $ast, array $arguments = [], Result &$result) : array
    {
        foreach ($ast['__prototypes'] as $prototypeName => $prototypeAst) {
            $propConfig = Arrays::getValueByPath($ast, ["__prototypes", $prototypeName, '__meta', 'styleguide', 'props']);
            if($propConfig) {
                $ast = Arrays::unsetValueByPath($ast, ["__prototypes", $prototypeName, '__meta', 'styleguide', 'props']);
            }
        }
        return $ast;
    }

}
