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
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var array
     */
    protected $decoded;

    /**
     * @var int
     */
    protected $status;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
        $this->status = $response->getStatusCode();
    }

    public function __call($name, $arguments)
    {
        return $this->response->{$name}(...$arguments);
    }

    public function toPsrResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return int
     */
    public function status()
    {
        return $this->status;
    }

    /**
     * @throws RuntimeException
     * @return string
     */
    public function body()
    {
        return (string) $this->response->getBody();
    }

    /**
     * @throws RuntimeException
     */
    public function object(): object
    {
        $contentType = $this->header('Content-Type');
        $contents = $this->body();

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
    public function toArray(): array
    {
        $contentType = $this->header('Content-Type');
        $contents = $this->body();

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
            $this->decoded = $this->toArray();
        }

        return array_get($this->decoded, $key, $default);
    }

    /**
     * @return string
     */
    public function header(string $header)
    {
        return $this->response->getHeaderLine($header);
    }
}
