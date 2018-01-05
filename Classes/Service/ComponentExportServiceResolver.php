<?php
namespace Sitegeist\Monocle\ComponentExport\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Reflection\ReflectionService;

class ComponentExportServiceResolver
{
    /**
     * @var ObjectManagerInterface
     * @Flow\Inject
     */
    protected $objectManager;

    /**
     * @var ReflectionService
     * @Flow\Inject
     */
    protected $reflectionService;

    /**
     * @param $format
     * @return ComponentExportServiceInterface
     */
    public function resolveExportService ($format): ComponentExportServiceInterface
    {
        $componentExportServiceClassNames = $this->reflectionService->getAllImplementationClassNamesForInterface(ComponentExportServiceInterface::class);
        /** @var $componentExportServiceClassName ComponentExportServiceInterface */
        foreach ($componentExportServiceClassNames as $componentExportServiceClassName) {
            if ($componentExportServiceClassName::getFormat() === $format) {
                return $this->objectManager->get($componentExportServiceClassName);
            }
        }
        throw new \Exception(sprintf('No export service for format %s found', $format));
    }
}
