<?php

declare(strict_types=1);
/**
 * This file is part of Smsease.
 *
 * @link     https://github.com/huangdijia/smsease
 * @document https://github.com/huangdijia/smsease/blob/main/README.md
 * @contact  huangdijia@gmail.com
 * @license  https://github.com/huangdijia/smsease/blob/main/LICENSE
 */
namespace Huangdijia\Smsease\Traits;

use GuzzleHttp\Client;
use Huangdijia\Smsease\Support\Response;

/**
 * Trait HasHttpRequest.
 */
trait HasHttpRequest
{
    /**
     * Make a get request.
     *
     * @param string $endpoint
     * @param array $query
     * @param array $headers
     *
     * @return Response
     */
    protected function get($endpoint, $query = [], $headers = [])
    {
        return $this->request('get', $endpoint, [
            'headers' => $headers,
            'query'   => $query,
        ]);
    }

    /**
     * Make a post request.
     *
     * @param string $endpoint
     * @param array $params
     * @param array $headers
     *
     * @return Response
     */
    protected function post($endpoint, $params = [], $headers = [])
    {
        return $this->request('post', $endpoint, [
            'headers'     => $headers,
            'form_params' => $params,
        ]);
    }

    /**
     * Make a post request with json params.
     *
     * @param string $endpoint
     * @param array $params
     * @param array $headers
     *
     * @return Response
     */
    protected function postJson($endpoint, $params = [], $headers = [])
    {
        return $this->request('post', $endpoint, [
            'headers' => $headers,
            'json'    => $params,
        ]);
    }

    /**
     * Make a http request.
     *
     * @param string $method
     * @param string $endpoint
     * @param array $options http://docs.guzzlephp.org/en/latest/request-options.html
     *
     * @return Response
     */
    protected function request($method, $endpoint, $options = [])
    {
        return new Response($this->getHttpClient($this->getBaseOptions())->{$method}($endpoint, $options));
    }

    /**
     * Return base Guzzle options.
     *
     * @return array
     */
    protected function getBaseOptions()
    {
        $options = method_exists($this, 'getGuzzleOptions') ? $this->getGuzzleOptions() : [];

        return \array_merge($options, [
            'base_uri' => method_exists($this, 'getBaseUri') ? $this->getBaseUri() : '',
            'timeout'  => method_exists($this, 'getTimeout') ? $this->getTimeout() : 5.0,
        ]);
    }

    /**
     * Return http client.
     *
     * @return \GuzzleHttp\Client
     *
     * @codeCoverageIgnore
     */
    protected function getHttpClient(array $options = [])
    {
        return new Client($options);
    }
}
