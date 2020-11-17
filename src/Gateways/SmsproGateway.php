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

use Throwable;
use Overtrue\EasySms\Support\Config;
use Overtrue\EasySms\Gateways\Gateway;
use Psr\Http\Message\ResponseInterface;
use Overtrue\EasySms\Traits\HasHttpRequest;
use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Exceptions\GatewayErrorException;

class SmsproGateway extends Gateway
{
    use HasHttpRequest;

    const ENDPOINT_URL = 'https://api3.hksmspro.com/service/smsapi.asmx/SendSMS';

    const SUCCESS_CODE = 1;

    protected static $stateMap = [
        1 => 'Message	Sent',
        0 => 'Missing	Values',
        10 => 'Incorrect Username or Password',
        20 => 'Message content too long',
        30 => 'Message content too long',
        40 => 'Telephone number too long',
        60 => 'Incorrect Country Code',
        70 => 'Balance not enough',
        80 => 'Incorrect date time',
        100 => 'System error, please try again',
    ];

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
            'Username' => $config->get('username'),
            'Password' => $config->get('password'),
            'Telephone' => $to->getNumber(),
            'UserDefineNo' => '00000',
            'Hex' => '',
            'Message' => $msg,
            'Sender' => $config->get('sender'),
        ];

        /** @var array|ResponseInterface */
        $response = $this->post(self::ENDPOINT_URL, $params, [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        $content = trim($response->getBody()->getContents());

        if ($content == '') {
            throw new GatewayErrorException('Response body is empty!', 402, $response);
        }
        try {
            $result = simplexml_load_string($content);
            $result = json_encode($result);
            $result = json_decode($result, true);
        } catch (Throwable $e) {
            throw new GatewayErrorException($e->getMessage(), 403, $response);
        }

        $state = $result['State'] ?? 0;

        if ($state != self::SUCCESS_CODE) {
            throw new GatewayErrorException(self::$stateMap[$state] ?? 'Unknown error', 500, $response);
        }

        return $result;
    }
}
