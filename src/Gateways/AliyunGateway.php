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

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Exceptions\GatewayErrorException;
use Overtrue\EasySms\Support\Config;

// Download：https://github.com/aliyun/openapi-sdk-php-client
// Usage：https://github.com/aliyun/openapi-sdk-php-client/blob/master/README-CN.md

class AliyunGateway extends Gateway
{
    const ENDPOINT_METHOD = 'SendMessageToGlobe';

    const ENDPOINT_VERSION = '2018-05-01';

    const ENDPOINT_FORMAT = 'JSON';

    const ENDPOINT_REGION_ID = 'cn-hangzhou';

    const ENDPOINT_SIGNATURE_METHOD = 'HMAC-SHA1';

    const ENDPOINT_SIGNATURE_VERSION = '1.0';

    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        AlibabaCloud::accessKeyClient($config->get('access_key_id'), $config->get('access_key_secret'))
            ->regionId('ap-southeast-1')
            ->asGlobalClient();

        $data     = $message->getData($this);
        $signName = ! empty($data['sign_name']) ? $data['sign_name'] : $config->get('sign_name');
        $msg      = $message->getContent($this);

        if (! empty($msg) && mb_substr($msg, 0, 1) != '【' && ! empty($signName)) {
            $msg = '【' . $signName . '】' . $msg;
        }

        unset($data['sign_name']);

        $action = self::ENDPOINT_METHOD;

        if ($to->getIDDCode() == '86') { // CN
            $action = 'SendMessageWithTemplate';
        }

        $query = [
            'To'      => ! \is_null($to->getIDDCode()) ? strval(str_replace('+', '', $to->getUniversalNumber())) : $to->getNumber(),
            'Message' => $msg,
        ];

        try {
            $result = AlibabaCloud::rpcRequest()
                ->product('Dysmsapi')
                ->host('dysmsapi.ap-southeast-1.aliyuncs.com')
                ->version(self::ENDPOINT_VERSION)
                ->action($action)
                ->method('POST')
                ->format(self::ENDPOINT_FORMAT)
                ->options([
                    'query' => $query,
                ])
                ->request();

            return $result->toArray();
        } catch (ClientException $e) {
            throw new GatewayErrorException($e->getErrorMessage(), 0, ['exception' => $e]);
        } catch (ServerException $e) {
            throw new GatewayErrorException($e->getErrorMessage(), 0, ['exception' => $e]);
        }
    }
}
