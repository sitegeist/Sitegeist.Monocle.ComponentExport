<?php
namespace Sitegeist\Monocle\ComponentExport\Service\AstFilter;

use Neos\Error\Messages\Result;

interface AstFilterInterface
{
    /**
     * @param array $ast
     * @param Result $result
     * @return array
     */
    public function process(array $ast, array $arguments = [], Result &$result) : array;
}
