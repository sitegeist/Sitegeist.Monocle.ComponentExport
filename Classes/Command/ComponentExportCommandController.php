<?php
namespace Sitegeist\Monocle\ComponentExport\Command;

/**
 * This file is part of the Sitegeist.Monocle.ComponentExport package
 *
 * (c) 2016
 * Martin Ficzel <ficzel@sitegeist.de>
 * Wilhelm Behncke <behncke@sitegeist.de>
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Sitegeist\Monocle\Fusion\FusionService;
use Sitegeist\Monocle\Service\DummyControllerContextTrait;
use Sitegeist\Monocle\Service\PackageKeyTrait;
use Neos\Utility\Arrays;
use Sitegeist\Monocle\ComponentExport\Service\ComponentExportServiceResolver;
use Sitegeist\Monocle\ComponentExport\Service\ComponentExportServiceInterface;

/**
 * Class StyleguideCommandController
 * @package Sitegeist\Monocle\Command
 */
class ComponentExportCommandController extends CommandController
{
    use DummyControllerContextTrait, PackageKeyTrait;

    /**
     * @Flow\Inject
     * @var FusionService
     */
    protected $fusionService;

    /**
     * @Flow\Inject
     * @var ComponentExportServiceResolver
     */
    protected $componentExportServiceResolver;

    /**
     * Get all styleguide items that are currently available
     *
     * @param string $format Result encoding ``fluid`` and ``smarty`` and ``react`` are supported
     * @param string $exportIdentifier Identifier to export a subset of prototypes
     * @param string $packageKey site-package (defaults to first found)
     */
    public function allCommand($format = 'fluid', $exportIdentifier = null, $packageKey = null)
    {
        $sitePackageKey = $packageKey ?: $this->getDefaultSitePackageKey();

        $componentPrototypes = $this->getFusionExportPrototypes($sitePackageKey, $exportIdentifier);

        $componentExportService = $this->componentExportServiceResolver->resolveExportService($format);
        $componentExportService->export('Data/Temporary/Export','Foo.Bar', $componentPrototypes);

    }

    /**
     * @param $sitePackageKey
     * @param null $exportIdentifier
     * @return array
     */
    protected function getFusionExportPrototypes($sitePackageKey, $exportIdentifier = null)
    {
        $enableExportConfigurationPath = '__meta.styleguide.options.export' . ($exportIdentifier ? '.' . $exportIdentifier : '');

        $fusionAst = $this->fusionService->getMergedTypoScriptObjectTreeForSitePackage($sitePackageKey);

        $exportPrototypes = [];
        if ($fusionAst && $fusionAst['__prototypes']) {
            foreach ($fusionAst['__prototypes'] as $prototypeFullName => $prototypeObject) {
                $enableExportConfiguration = Arrays::getValueByPath($prototypeObject, $enableExportConfigurationPath);
                if ($enableExportConfiguration) {
                    $exportPrototypes[$prototypeFullName] = $prototypeObject;
                }
            }
        }

        return $exportPrototypes;
    }

}
