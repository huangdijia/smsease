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

use Huangdijia\Smsease\Contracts\GatewayInterface;
use Overtrue\EasySms\Support\Config;
use RuntimeException;

abstract class Gateway extends \Overtrue\EasySms\Gateways\Gateway implements GatewayInterface
{
    /**
     * @throws RuntimeException
     * @return array
     */
    public function getBalance(Config $config)
    {
        throw new RuntimeException(static::class . ' doesnt support get balance');
    }
}
