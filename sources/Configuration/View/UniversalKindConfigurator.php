<?php

namespace Moro\Indexer\Common\Configuration\View;

use Moro\Indexer\Common\Configuration\ConfigurationInterface;
use Moro\Indexer\Common\Configuration\ConfiguratorInterface;

/**
 * Class UniversalKindConfigurator
 * @package Moro\Indexer\Common\Configuration\View
 */
class UniversalKindConfigurator implements ConfiguratorInterface
{
    /**
     * @param ConfigurationInterface $configuration
     * @param \Moro\Indexer\Common\View\Kind\UniversalKind $kind
     */
    public function apply(ConfigurationInterface $configuration, $kind)
    {
        $ns = 'types/{code}/kinds/{kind}';

        $kind->setCode($configuration->getContext()['kind']);
        $kind->setTemplate($configuration->get("$ns/template") ?? '');

        foreach ($configuration->get("$ns/parameters") ?? [] as $name => $path) {
            $kind->addParameter($name, $path);
        }
    }
}