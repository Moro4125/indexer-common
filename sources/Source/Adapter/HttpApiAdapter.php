<?php

namespace Moro\Indexer\Common\Source\Adapter;

use Moro\Indexer\Common\Accessories\ArraysGetByPathTrait;
use Moro\Indexer\Common\Accessories\HttpRequest2ServerTrait;
use Moro\Indexer\Common\Source\AdapterInterface;
use Moro\Indexer\Common\Source\Exception\NotFoundException;

/**
 * Class HttpApiAdapter
 * @package Moro\Indexer\Common\Source\Adapter
 */
class HttpApiAdapter implements AdapterInterface
{
    use ArraysGetByPathTrait;
    use HttpRequest2ServerTrait;

    private $_proxy;
    private $_urlIdList;
    private $_urlEntityById;
    private $_basicAuth;
    private $_usePostMethod;

    public function setProxy(string $proxy)
    {
        $this->_proxy = $proxy;
    }

    public function setUsePostMethod(bool $flag)
    {
        $this->_usePostMethod = $flag;
    }

    public function setUrlIdList(string $url, string $keyFrom = null, string $keyLimit = null, string $keyResult = null)
    {
        $this->_urlIdList = [$url, $keyFrom, $keyLimit, $keyResult];
    }

    public function setUrlEntityById(string $url, string $keyId = null, string $keyResult = null)
    {
        $this->_urlEntityById = [$url, $keyId, $keyResult];
    }

	public function setBasicAuthorization(string $user = null, string $password = null)
    {
		if (is_string($user) && is_null($password) && strpos($user, ':')) {
			list($user, $password) = explode(':', $user, 2);
		}

        $this->_basicAuth['user'] = $user;
        $this->_basicAuth['pass'] = $password;
    }

    public function receiveIdList(int $from, int $limit): array
    {
        list($url, $keyFrom, $keyLimit, $keyResult) = $this->_urlIdList;

        $post = $this->_usePostMethod;
        $data = [$keyFrom ?? 'from' => $from, $keyLimit ?? 'limit' => $limit];
        $response = $this->_doRequest($url, $data, $post, $this->_proxy);

        if ($keyResult) {
            $result = $this->_getByPath($keyResult, $response, $flag);
            $result = empty($flag) ? reset($result) : $result;

            if (!is_array($result)) {
                throw new NotFoundException();
            }

            return $result;
        }

        return $response;
    }

    public function receiveEntityById(string $id): array
    {
        list($url, $keyId, $keyResult) = $this->_urlEntityById;

        $response = $this->_doRequest($url, [$keyId ?? 'id' => $id], $this->_usePostMethod, $this->_proxy);

        if ($keyResult) {
            $result = $this->_getByPath($keyResult, $response, $flag);
            $result = empty($flag) ? reset($result) : $result;

            if (!is_array($result)) {
                throw new NotFoundException();
            }

            return $result;
        }

        return $response;
    }
}