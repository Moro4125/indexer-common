<?php

namespace Moro\Indexer\Common\Configuration\Source;

use Moro\Indexer\Common\Configuration\ConfigurationInterface;
use Moro\Indexer\Common\Configuration\ConfiguratorInterface;

/**
 * Class HttpApiAdapterConfigurator
 * @package Moro\Indexer\Common\Configuration\Source
 */
class HttpApiAdapterConfigurator implements ConfiguratorInterface
{
    /**
     * @param ConfigurationInterface $configuration
     * @param \Moro\Indexer\Common\Source\Adapter\HttpApiAdapter $adapter
     */
    public function apply(ConfigurationInterface $configuration, $adapter)
    {
        if ($configuration->has('types/{code}/source-adapter/method')) {
            $method = $configuration->get('types/{code}/source-adapter/method');
            $adapter->setUsePostMethod(strtoupper($method) == 'POST');
        }

        if ($configuration->get('types/{code}/source-adapter/id-list|is_array')) {
            $url = $configuration->get('types/{code}/source-adapter/id-list/url');
            $from = $configuration->get('types/{code}/source-adapter/id-list/from') ?? 'from';
            $limit = $configuration->get('types/{code}/source-adapter/id-list/limit') ?? 'limit';
            $result = $configuration->get('types/{code}/source-adapter/id-list/result');
        } else {
            $chunks = explode(',', $configuration->get('types/{code}/source-adapter/id-list'));
            $chunks = array_slice(array_merge($chunks, [null, null, null]), 0, 4);
            list($url, $from, $limit, $result) = $chunks;
        }

        $adapter->setUrlIdList($url, $from, $limit, $result);

        if ($configuration->get('types/{code}/source-adapter/entity|is_array')) {
            $id = $configuration->get('types/{code}/source-adapter/entity/id') ?? 'id';
            $url = $configuration->get('types/{code}/source-adapter/entity/url');
            $result = $configuration->get('types/{code}/source-adapter/entity/result');
        } else {
            $chunks = explode(',', $configuration->get('types/{code}/source-adapter/entity'));
            $chunks = array_slice(array_merge($chunks, [null, null]), 0, 3);
            list($url, $id, $result) = $chunks;
        }

        $adapter->setUrlEntityById($url, $id, $result);

        if ($configuration->has('types/{code}/source-adapter/auth')) {
            if ($configuration->get('types/{code}/source-adapter/auth|is_array')) {
                $username = $configuration->get('types/{code}/source-adapter/auth/username');
                $password = $configuration->get('types/{code}/source-adapter/auth/password');
            } else {
                $chunks = explode(':', $configuration->get('types/{code}/source-adapter/auth'));
                $chunks = array_slice(array_merge($chunks, [null]), 0, 2);
                list($username, $password) = $chunks;
            }

            $adapter->setBasicAuthorization($username, $password);
        }
    }
}