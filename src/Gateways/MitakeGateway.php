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
namespace Huangdijia\Smsease\Gateways;

use Huangdijia\Smsease\Traits\HasHttpRequest;
use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Exceptions\GatewayErrorException;
use Overtrue\EasySms\Support\Config;

class MitakeGateway extends Gateway
{
    use HasHttpRequest;

    const ENDPOINT_URL = 'http://smexpress.mitake.com.tw:9600/SmSendGet.asp';

    const SUCCESS_CODE = 1;

    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        $data     = $message->getData($this);
        $signName = ! empty($data['sign_name']) ? $data['sign_name'] : $config->get('sign_name', '');

        unset($data['sign_name']);

        $msg = $message->getContent($this);

        if (! empty($msg) && mb_substr($msg, 0, 1) != '【' && ! empty($signName)) {
            $msg = '【' . $signName . '】' . $msg;
        }

        $params = [
            'username' => $config->get('username'),
            'password' => $config->get('password'),
            'type'     => $config->get('type') ?? 'now',
            'encoding' => $config->get('encoding') ?? 'big5',
            'dstaddr'  => $to->getNumber(),
            'smbody'   => iconv('utf-8', $config->get('encoding') ?? 'big5', $msg),
            // 'vldtime'    => $config->get('vldtime'),
            // 'dlvtime'    => $config->get('dlvtime'),
        ];

        $response = $this->get(self::ENDPOINT_URL, $params);
        $result   = $this->parseResponse($response->body());

        if ($result['statuscode'] != self::SUCCESS_CODE) {
            throw new GatewayErrorException($result['Error'], $result['statuscode'], ['response' => $response]);
        }

        return $result;
    }

    /**
     * Parse respose.
     * @param string $content
     * @return (int|string)[]|array
     */
    private function parseResponse($content = '')
    {
        $default = [
            'statuscode' => 0,
            'Error'      => 'Parse response failed',
        ];

        if (empty($content)) {
            return $default;
        }

        preg_match_all('/(\w+)=([^\r\n]+)/i', $content, $matches);

        if (empty($matches)) {
            return $default;
        }

        $result = ['Error' => ''];

        foreach ($matches[1] as $i => $key) {
            $result[$key] = isset($matches[2][$i]) ? $matches[2][$i] : '';

            if ($key == 'Error') {
                $result[$key] = iconv('big5', 'utf-8', $result[$key]);
            }
        }

        return array_merge($default, $result);
    }
}
