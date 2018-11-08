<?php

namespace Moro\Indexer\Common\Accessories;

use Moro\Indexer\Common\Source\Exception\AdapterFailedException;
use Moro\Indexer\Common\Source\Exception\NotFoundException;

/**
 * Trait HttpRequest2ServerTrait
 * @package Moro\Indexer\Common\Accessories
 */
trait HttpRequest2ServerTrait
{
    private $_cookies;

    protected function _doRequest(string $url, array $data, bool $usePostMethod = null, string $proxy = null): array
    {
        if (null === $post = ($usePostMethod ? $data : null)) {
            $query = http_build_query($data);
            $url .= (strpos($url, '?') ? '&' : '?') . $query;
        }

        $headers = [
            'Accept-Type: application/json',
            'Accept-Charset: utf-8, *;q=0.1',
        ];

		if (function_exists('gzdecode')) {
			$headers[] = 'Accept-Encoding: gzip';
		}

        foreach ($this->_cookies ?? [] as $name => $value) {
            $headers[] = 'Cookie: ' . $name . '=' . $value;
        }

        if (isset($this->_basicAuth)) {
            $headers[] = 'Authorization: Basic ' . base64_encode(implode(':', $this->_basicAuth));
        }

		$headers[] = 'Connection: close';

        $response = $this->_sendRequest($url, $post, $headers, $proxy);

        if (isset($headers['Response-Code']) && $headers['Response-Code'] == 404) {
            throw new NotFoundException();
        } elseif (empty($headers['Response-Code']) || $headers['Response-Code'] != 200) {
            throw new AdapterFailedException('Bad HTTP response code ' . $headers['Response-Code']);
        }

        if (isset($headers['Set-Cookie'])) {
            foreach (explode(PHP_EOL, $headers['Set-Cookie']) as $cookie) {
                if ($cookie = trim(explode(';', $cookie)[0])) {
                    list($name, $value) = array_map('trim', explode('=', $cookie));
                    $this->_cookies[$name] = $value;
                }
            }
        }

        return json_decode($response, true);
    }

    protected function _sendRequest(
        string $url,
        array $post = null,
        array &$headers = null,
        string $proxy = null
    ): string
    {
		$scheme = (strpos($url, 'https://') === 0) ? 'https' : 'http';

		$context[$scheme]['protocol_version'] = '1.1';
		$context[$scheme]['method'] = is_null($post) ? 'GET' : 'POST';
		$context[$scheme]['ignore_errors'] = true;

        if (isset($post)) {
            ob_start();
            echo http_build_query($post, '', '&');
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            $headers[] = 'Content-Length: ' . ob_get_length();
			$context[$scheme]['content'] = ob_get_clean();
        }

        if ($proxy) {
			$context[$scheme]['proxy'] = $proxy;
			$context[$scheme]['request_fulluri'] = true;
        }

        $headers[] = '';
		$context[$scheme]['header'] = implode("\r\n", $headers);

        $context = stream_context_create($context);
        $response = file_get_contents($url, false, $context);
        $headers = [];

        foreach ($http_response_header as $v) {
            $t = explode(':', $v, 2);

            if (isset($t[1]) && $key = trim($t[0])) {
                if (isset($headers[$key])) {
                    $headers[$key] = PHP_EOL . trim($t[1]);
                } else {
                    $headers[$key] = trim($t[1]);
                }
            } elseif (preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#", $v, $out)) {
                $headers['Response-Code'] = intval($out[1]);
            } else {
                $headers[] = $v;
            }
        }

        if (isset($headers['Content-Encoding']) && false !== strpos($headers['Content-Encoding'], 'gzip')) {
			if (function_exists('gzdecode')) {
				/** @noinspection PhpComposerExtensionStubsInspection */
				$response = gzdecode($response);
			}
        }

        return (string)$response;
    }
}