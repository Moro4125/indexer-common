<?php

namespace Moro\Indexer\Common\Configuration\Source;

use Moro\Indexer\Common\Configuration\ConfigurationInterface;
use Moro\Indexer\Common\Configuration\ConfiguratorInterface;

/**
 * Class DoctrineDBALAdapterConfigurator
 * @package Moro\Indexer\Common\Configuration\Source
 */
class DoctrineDBALAdapterConfigurator implements ConfiguratorInterface
{
    /**
     * @param ConfigurationInterface $configuration
     * @param \Moro\Indexer\Common\Source\Adapter\DoctrineDBALAdapter $adapter
     */
    public function apply(ConfigurationInterface $configuration, $adapter)
    {
        $adapter->setTable($configuration->get('types/{code}/source-adapter/table'));

        if ($configuration->has('types/{code}/source-adapter/columns|is_array')) {
            $adapter->setAlias2Column($configuration->get('types/{code}/source-adapter/columns'));
        }
    }
}