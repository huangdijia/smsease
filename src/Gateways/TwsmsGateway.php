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

use Overtrue\EasySms\Support\Config;
use Overtrue\EasySms\Gateways\Gateway;
use Psr\Http\Message\ResponseInterface;
use Overtrue\EasySms\Traits\HasHttpRequest;
use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Exceptions\GatewayErrorException;

class TwsmsGateway extends Gateway
{
    use HasHttpRequest;

    const ENDPOINT_URL = 'http://api.twsms.com/send.php';

    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        $data = $message->getData($this);
        $signName = ! empty($data['sign_name']) ? $data['sign_name'] : $config->get('sign_name', '');

        unset($data['sign_name']);

        $msg = $message->getContent($this);

        if (! empty($msg) && mb_substr($msg, 0, 1) != '【' && ! empty($signName)) {
            $msg = '【' . $signName . '】' . $msg;
        }

        $params = [
            'username' => $config->get('account'),
            'password' => $config->get('password'),
            'type' => $config->get('type') ?? 'now',
            'encoding' => $config->get('encoding') ?? 'big5',
            'vldtime' => $config->get('vldtime'),
            'dlvtime' => $config->get('dlvtime'),
            'mobile' => $to,
            'message' => iconv('utf-8', $config->get('encoding') ?? 'big5' . '//IGNORE', $msg),
        ];

        /** @var array|ResponseInterface */
        $response = $this->post(self::ENDPOINT_URL, $params);

        [$key, $msgId] = explode('=', $response->getBody()->getContents());

        if ($msgId <= 0) {
            throw new GatewayErrorException('Send failed', 0, $response);
        }

        return [
            'key' => $key,
            'msg_id' => $msgId,
        ];
    }
}
