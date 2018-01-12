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
class StyleguideExportCommandController extends CommandController
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
     * @param string $filename The file to export the ast to
     * @param string $packageKey site-package (defaults to first found)
     * @param string $exportNamespace Only prototypes with this namespace-prefix are exported
     * @param string $exportConfigurationPath Only prototypes that have this flag
     *
     */
    public function astCommand($format = 'fluid', $filename, $packageKey = null, $exportNamespace = null, $exportConfigurationPath = '__meta.styleguide.options.export')
    {
        $sitePackageKey = $packageKey ?: $this->getDefaultSitePackageKey();
        $componentExportService = $this->componentExportServiceResolver->resolveExportService($format);

        $fusionAst = $this->fusionService->getMergedFusionObjectTreeForSitePackage($sitePackageKey);
        // we use only the prototypes section in here
        $fusionAst = ['__prototypes' => $fusionAst['__prototypes']];

        $exportPrototypes = $componentExportService->detectExportPrototypes($fusionAst, $exportNamespace, $exportConfigurationPath);

        $componentExportService->exportAst($fusionAst, $exportPrototypes, $filename);

        $this->outputLine();
        $this->outputLine('Exported the ast for %s Components' , [count($exportPrototypes)]);
        $this->outputLine();

        foreach ($exportPrototypes as $exportPrototype) {
            $this->outputLine('- %s', [$exportPrototype]);
        }
    }

    /**
     * Get all styleguide items that are currently available
     *
     * @param string $format Result encoding ``fluid`` and ``smarty`` and ``react`` are supported
     * @param string $directory The package to export the component wrappers to
     * @param string $packageKey site-package (defaults to first found)
     * @param string $exportNamespace Only prototypes with this namespace-prefix are exported
     * @param string $exportConfigurationPath Only prototypes that have this flag
     *
     */
    public function wrapperCommand($format = 'fluid', $directory, $packageKey = null, $exportNamespace = null, $exportConfigurationPath = '__meta.styleguide.options.export')
    {
        $sitePackageKey = $packageKey ?: $this->getDefaultSitePackageKey();
        $componentExportService = $this->componentExportServiceResolver->resolveExportService($format);

        $fusionAst = $this->fusionService->getMergedFusionObjectTreeForSitePackage($sitePackageKey);
        // we use only the prototypes section in here
        $fusionAst = ['__prototypes' => $fusionAst['__prototypes']];

        $exportPrototypes = $componentExportService->detectExportPrototypes($fusionAst, $exportNamespace, $exportConfigurationPath);

        $componentExportService->exportWrapper($fusionAst, $exportPrototypes, $directory, $exportNamespace);

        $this->outputLine();
        $this->outputLine('Created wrapper for %s Components' , [count($exportPrototypes)]);
        $this->outputLine();

        foreach ($exportPrototypes as $exportPrototype) {
            $this->outputLine('- %s', [$exportPrototype]);
        }

    }
}
