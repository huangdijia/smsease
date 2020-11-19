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
namespace Huangdijia\Smsease\Contracts;

use Overtrue\EasySms\Support\Config;

interface GatewayInterface extends \Overtrue\EasySms\Contracts\GatewayInterface
{
    /**
     * @return array
     */
    public function getBalance(Config $config);
}
