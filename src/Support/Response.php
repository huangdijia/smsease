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
namespace Huangdijia\Smsease\Support;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class Response
{
    protected $response;

    protected $decoded;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function __call($name, $arguments)
    {
        return $this->{$name}(...$arguments);
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return int
     */
    public function status()
    {
        return $this->response->getStatusCode();
    }

    /**
     * @throws RuntimeException
     * @return string
     */
    public function body()
    {
        return $this->response->getBody()->getContents();
    }

    /**
     * @throws RuntimeException
     */
    public function object(): object
    {
        $contentType = $this->response->getHeaderLine('Content-Type');
        $contents = $this->response->getBody()->getContents();

        if (stripos($contentType, 'json') !== false || stripos($contentType, 'javascript')) {
            return json_decode($contents);
        }

        if (stripos($contentType, 'xml') !== false) {
            return json_decode(json_encode(@simplexml_load_string($contents)));
        }

        throw new RuntimeException('Response Content_Type is not json or xml');
    }

    /**
     * @throws RuntimeException
     */
    public function array(): array
    {
        $contentType = $this->response->getHeaderLine('Content-Type');
        $contents = $this->response->getBody()->getContents();

        if (stripos($contentType, 'json') !== false || stripos($contentType, 'javascript')) {
            return json_decode($contents, true);
        }

        if (stripos($contentType, 'xml') !== false) {
            return json_decode(json_encode(@simplexml_load_string($contents)), true);
        }

        throw new RuntimeException('Response Content_Type is not json or xml');
    }

    /**
     * @param null|string $key
     * @param null|mixed $default
     * @throws RuntimeException
     * @return mixed
     */
    public function json($key = null, $default = null)
    {
        if (is_null($this->decoded)) {
            $this->decoded = $this->array();
        }

        return array_get($this->decoded, $key, $default);
    }
}
