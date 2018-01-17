<?php
namespace Sitegeist\Monocle\ComponentExport\Service\FusionAstFilter;

use Neos\Error\Messages\Result;

interface FusionAstFilterInterface
{
    /**
     * @param array $ast
     * @param Result $result
     * @param array $arguments
     * @return array
     */
    public function process(array $ast, Result &$result, array $arguments = []) : array;
}
