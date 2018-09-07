<?php

namespace Moro\Indexer\Common\Source\Adapter\Debug;

use Moro\Indexer\Common\Source\Adapter\HttpApiAdapter;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class HttpApiDebugAdapter
 * @package Moro\Indexer\Common\Source\Adapter\Debug
 */
class HttpApiDebugAdapter extends HttpApiAdapter
{
    /** @var LoggerInterface */
    protected $_logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }

    /**
     * @param string $url
     * @param array|null $post
     * @param array|null $headers
     * @param string|null $proxy
     * @return string
     * @throws Throwable
     */
    protected function _sendRequest(
        string $url,
        array $post = null,
        array &$headers = null,
        string $proxy = null
    ): string
    {
        $message = sprintf('> %1$s %2$s', isset($post) ? 'POST' : 'GET', $url);
        $this->_logger->debug($message, $headers ?? []);
        isset($post) && $this->_logger->debug('>', $post);

        try {
            $response = parent::_sendRequest($url, $post, $headers, $proxy);
        } catch (Throwable $e) {
            $message = '< Status: 520, Exception: %1$s, %2$s | %3$s:%4$s | %5$s';
            $message = sprintf($message, get_class($e), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getCode());
            $this->_logger->error($message);
            throw $e;
        }

        $message = sprintf('< Status: %1$s, Length: %2$s', $headers['Response-Code'] ?? 0, strlen($response));
        $this->_logger->debug($message, $headers ?? []);

        return $response;
    }
}