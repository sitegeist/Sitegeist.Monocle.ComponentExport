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
     * @param string $filename The file to export the ast to
     * @param string $packageKey site-package (defaults to first found)
     */
    public function forFormatCommand($format = 'fluid', $filename, $packageKey = null)
    {
        $sitePackageKey = $packageKey ?: $this->getDefaultSitePackageKey();
        $fusionAst = $this->fusionService->getMergedFusionObjectTreeForSitePackage($sitePackageKey);
        $componentExportService = $this->componentExportServiceResolver->resolveExportService($format);
        $componentExportService->export($fusionAst, $filename);
    }

}
