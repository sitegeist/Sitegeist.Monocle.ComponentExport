<?php
namespace Sitegeist\Monocle\ComponentExport\Service\FusionAstFilter;

use Neos\Error\Messages\Notice;
use Neos\Error\Messages\Result;
use Neos\Utility\Arrays;

class CreateRenderPathes extends PrototypeListBasedFilter implements FusionAstFilterInterface
{
    /**
     * @param array $ast
     * @param Result $result
     * @param array $prototypeNames
     * @return array
     */
    protected function processInternal(array $ast, Result &$result, array $prototypeNames = []) : array
    {
        foreach ($prototypeNames as $prototypeName) {
            //
            // create rendering ast-segment
            //
            $prototypeAst = Arrays::getValueByPath($ast, ['__prototypes', $prototypeName]);
            if (!$prototypeAst) {
                continue;
            }

            $renderingAst = [
                "__objectType" => $prototypeName,
                "__eelExpression" => null,
                "__value" => null
            ];

            //
            // fetch all props that are defined in propTypes from context
            //
            $prototypePropTypes =  Arrays::getValueByPath($prototypeAst, ['__meta', 'propTypes']);
            if ($prototypePropTypes && is_array($prototypePropTypes)) {
                foreach (array_keys($prototypePropTypes) as $propName) {
                    $renderingAst[$propName] = [
                        "__objectType" => null,
                        "__eelExpression" => $propName,
                        "__value" => null
                    ];
                }
            }

            //
            // store render ast in path
            //
            $renderPath = 'render_' . str_replace([':','.'], '_', $prototypeName);
            $ast[$renderPath] = $renderingAst;

            //
            // store notice
            //
            $notice = new Notice(sprintf('Exported prototype "%s" to path "%s"', $prototypeName, $renderPath));
            $result->forProperty($prototypeName)->addNotice($notice);
        }

        return $ast;
    }


    /**
     * @param array $fusionAst
     * @param array $prototypeNames
     * @return array
     */
    protected function createPrototypesRenderingPathes(array $fusionAst, array $prototypeNames)
    {

        foreach ($prototypeNames as $prototypeName) {
            //
            // create rendering ast-segment
            //
            $prototypeAst = Arrays::getValueByPath($fusionAst, ['__prototypes', $prototypeName]);
            $renderingAst = [
                "__objectType" => $prototypeName,
                "__eelExpression" => null,
                "__value" => null
            ];

            //
            // fetch all props that are defined in propTypes from context
            //
            $prototypePropTypes =  Arrays::getValueByPath($prototypeAst, ['__meta', 'propTypes']);
            if ($prototypePropTypes && is_array($prototypePropTypes)) {
                foreach (array_keys($prototypePropTypes) as $propName) {
                    $renderingAst[$propName] = [
                        "__objectType" => null,
                        "__eelExpression" => $propName,
                        "__value" => null
                    ];
                }
            }

            //
            // store render ast in path
            //
            $renderPath = $this->getFusionRenderPathForPrototypeName($prototypeName);
            $fusionAst[$renderPath] = $renderingAst;
        }
        return $fusionAst;
    }
}
