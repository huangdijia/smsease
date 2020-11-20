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
namespace Huangdijia\Smsease;

use Huangdijia\Smsease\Contracts\GatewayInterface;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Support\Config;
use RuntimeException;

/**
 * @method \Huangdijia\Smsease\Gateways\Gateway gateway(?string $gateway)
 */
class Smsease extends EasySms
{
    /**
     * @throws RuntimeException
     * @return array
     */
    public function getBalance(string $gateway = '')
    {
        $setting = new Config($this->config->get('gateways.' . $gateway, []));
        $gateway = $this->gateway($gateway);

        if (! in_array(GatewayInterface::class, class_implements($gateway))) {
            throw new RuntimeException(static::class . ' doesnt support get balance');
        }

        return $gateway->getBalance($setting);
    }
}
