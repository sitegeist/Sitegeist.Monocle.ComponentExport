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

use Imagine\Image\Fill\FillInterface;
use Neos\Error\Messages\Result;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Utility\Arrays;
use Neos\Utility\PositionalArraySorter;

use Sitegeist\Monocle\ComponentExport\Service\AstFilter\AstFilterInterface;
use Sitegeist\Monocle\Fusion\FusionService;
use Sitegeist\Monocle\Service\DummyControllerContextTrait;
use Sitegeist\Monocle\Service\PackageKeyTrait;
use Sitegeist\Monocle\ComponentExport\Service\FusionExportService;

/**
 * Class StyleguideCommandController
 * @package Sitegeist\Monocle\Command
 */
class FusionExportCommandController extends CommandController
{
    use DummyControllerContextTrait, PackageKeyTrait;

    /**
     * @var array
     * @Flow\InjectConfiguration("presets")
     */
    protected $presets;

    /**
     * @Flow\Inject
     * @var FusionService
     */
    protected $fusionService;

    /**
     * List all configured fusion export configurations
     */
    public function listCommand()
    {
        if ($this->presets) {
            $this->outputLine();
            $this->outputLine('<b>The following presets are configured</b>');
            $this->outputLine();
            foreach ($this->presets as $name => $presetConfiguration) {
                $description = array_key_exists('description', $presetConfiguration) ? $presetConfiguration['description'] : '';
                $this->outputLine(sprintf(' - %s: %s', $name, $description));
            }
            $this->outputLine();
        } else {
            $this->outputLine('No presets found in configuration');
            $this->quit(1);
        }
    }

    /**
     * Get all styleguide items that are currently available
     *
     * @param string $filename The file to export the ast to
     * @param string $preset The preset for this export
     * @param string $packageKey site-package (defaults to first found)
     *
     */
    public function presetCommand($filename = null, $preset = 'default', $packageKey = null)
    {
          if (!array_key_exists($preset, $this->presets)) {
            $this->outputLine(sprintf('Presets "%s" was not found in configuration', $preset));
            $this->quit(1);
        }

        $presetConfiguration = $this->presets[$preset];

        $sitePackageKey = $packageKey ?: $this->getDefaultSitePackageKey();
        $fusionAst = $this->fusionService->getMergedFusionObjectTreeForSitePackage($sitePackageKey);
        $result = new Result();

        //
        // sort and apply filters
        //
        $arraySorter = new PositionalArraySorter($presetConfiguration['filters']);
        $presetFilterConfigurations = $arraySorter->toArray();

        if ($presetFilterConfigurations && is_array($presetFilterConfigurations)) {
            foreach ($presetFilterConfigurations as $presetFilterConfiguration) {
                $class = Arrays::getValueByPath($presetFilterConfiguration, 'class');
                $arguments = Arrays::getValueByPath($presetFilterConfiguration, 'arguments');
                $arguments = $arguments ?: [];
                $filter = new $class();
                if ($filter instanceof AstFilterInterface) {
                    $fusionAst = $filter->process($fusionAst, $arguments, $result);
                }
            }
        }

        //
        // safe to file or return ast
        //
        if ($filename == null) {
            $this->output( json_encode($fusionAst, JSON_PRETTY_PRINT) );
            $this->quit();
        } else {
            file_put_contents(
                $filename,
                json_encode($fusionAst, JSON_PRETTY_PRINT)
            );
        }

        $this->outputLine();
        $this->outputLine(sprintf('<b>Exported the fusion ast with preset "%s" Components to file "%s"</b>', $preset, $filename));
        $this->outputLine();

        if ($result->hasNotices()) {
            $notices = $result->getFlattenedNotices();
            /** @var Error $error */
            foreach ($notices as $path => $pathNotice) {
                foreach ($pathNotice as $notice) {
                    $this->outputLine(' - %s -> %s', [$path, $notice->render()]);
                }
            }
        }
    }

    /**
     * @param string $filename
     * @param string $packageKey
     * @param string $namespace
     */
    public function prototypesCommand($filename, $packageKey = null, $namespace)
    {

    }
}
