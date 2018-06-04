<?php

namespace Moro\Indexer\Common\Configuration\Source;

use Moro\Indexer\Common\Configuration\ConfigurationInterface;
use Moro\Indexer\Common\Configuration\ConfiguratorInterface;

/**
 * Class UniversalNormalizerConfigurator
 * @package Moro\Indexer\Common\Configuration\Source
 */
class UniversalNormalizerConfigurator implements ConfiguratorInterface
{
    /**
     * @param ConfigurationInterface $configuration
     * @param \Moro\Indexer\Common\Source\Normalizer\UniversalNormalizer $normalizer
     */
    public function apply(ConfigurationInterface $configuration, $normalizer)
    {
        $ns = 'types/{code}/normalizers/{index}';

        foreach ($configuration->get("$ns/conditions/*") ?? [] as $condition) {
            $normalizer->addCondition($condition);
        }

        foreach ($configuration->get("$ns/rules") ?? [] as $from => $to) {
            if (is_numeric($from)) {
                list($from, $to) = is_array($to) ? $to : explode('=>', $to);
            }

            $normalizer->addRule($from, $to);
        }
    }
}