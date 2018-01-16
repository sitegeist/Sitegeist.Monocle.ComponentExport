<?php
namespace Sitegeist\Monocle\ComponentExport\Service\AstFilter;

use Neos\Error\Messages\Notice;
use Neos\Error\Messages\Result;
Use Neos\Utility\Arrays;

class CreateRenderPathesFilter extends PrototypeListBasedFilter implements AstFilterInterface
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

        foreach ($prototypeNames as $prototypeName) {

            //
            // create rendering ast-segment
            //
            $prototypeAst = Arrays::getValueByPath($ast, ['__prototypes', $prototypeName]);
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
                $renderingAst['__meta']['propTypes'] = $prototypePropTypes;
                foreach (array_keys($prototypePropTypes)  as $propName) {
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

            $renderPath = 'renderPrototype_' . str_replace( [':','.'] , '_', $prototypeName);
            $ast[$renderPath] = $renderingAst;
            $result->forProperty($prototypeName)->addNotice(new Notice(sprintf('Exported prototype "%s" to path "%s"', $prototypeName, $renderPath)));
        }

        return $ast;
    }


    /**
     * @param array $fusionAst
     * @param array $prototypeNames
     * @return array
     */
    protected function createPrototypesRenderingPathes(array $fusionAst, array $prototypeNames) {

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
                foreach (array_keys($prototypePropTypes)  as $propName) {
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
