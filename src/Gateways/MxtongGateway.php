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
use Overtrue\EasySms\Gateways\Gateway;
use Overtrue\EasySms\Support\Config;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class MxtongGateway extends Gateway
{
    use HasHttpRequest;

    const ENDPOINT_URL = 'http://www.mxtong.cn:8080/GateWay/Services.asmx/DirectSend';

    const SUCCESS_CODE = 'Sucess';

    /**
     * <?xml version="1.0" encoding="utf-8"?>
     * <ROOT xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="JobSendedDescription">
     *   <RetCode>Sucess</RetCode>
     *   <JobID>71787727</JobID>
     *   <OKPhoneCounts>1</OKPhoneCounts>
     *   <StockReduced>1</StockReduced>
     *   <ErrPhones />
     * </ROOT>.
     * @throws RuntimeException
     * @throws GatewayErrorException
     * @return array
     */
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
            'Phones' => $to->getNumber(),
            'Content' => $msg,
            'SendTime' => '',
            'UserId' => $config->get('user_id'),
            'Account' => $config->get('account'),
            'Password' => $config->get('password'),
            'SendType' => $config->get('send_type') ?? 1,
            'PostFixNumber' => $config->get('post_fix_number') ?? 1,
        ];

        /** @var array|ResponseInterface|string $response */
        $result = $this->post(self::ENDPOINT_URL, $params, [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        if (! $result) {
            throw new GatewayErrorException('Parse xml failed', 402, ['result' => $result]);
        }

        if (! isset($result['RetCode']) || $result['RetCode'] != self::SUCCESS_CODE) {
            throw new GatewayErrorException($result['Message'], 402, ['result' => $result]);
        }

        if (isset($result['OKPhoneCounts']) && $result['OKPhoneCounts'] == 0) {
            throw new GatewayErrorException($result['Message'], 403, ['result' => $result]);
        }

        return $result;
    }
}
