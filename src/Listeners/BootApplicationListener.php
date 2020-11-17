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
namespace Huangdijia\Smsease\Listeners;

use Huangdijia\Smsease\Gateways\AccessyouGateway;
use Huangdijia\Smsease\Gateways\MitakeGateway;
use Huangdijia\Smsease\Gateways\MxtongGateway;
use Huangdijia\Smsease\Gateways\SmsproGateway;
use Huangdijia\Smsease\Gateways\TwsmsGateway;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Overtrue\EasySms\EasySms;

class BootApplicationListener implements ListenerInterface
{
    /**
     * 返回一个该监听器要监听的事件数组，可以同时监听多个事件.
     */
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * 相当于 Laravel 的 ServiceProvider::boot.
     * @param BootApplication $event
     */
    public function process(object $event)
    {
        /** @var \Hyperf\Di\Container */
        $container = \Hyperf\Utils\ApplicationContext::getcontainer();
        /** @var ConfigInterface $config */
        $config = $container->get(ConfigInterface::class);

        $container->set(EasySms::class, tap(new EasySms($config->get('easysms') ?? []), function ($easySms) {
            /* @var \Overtrue\EasySms\EasySms $easySms */
            $easySms->extend('accessyou', function ($config) {
                return new AccessyouGateway($config);
            });
            $easySms->extend('mitake', function ($config) {
                return new MitakeGateway($config);
            });
            $easySms->extend('mxtong', function ($config) {
                return new MxtongGateway($config);
            });
            $easySms->extend('smspro', function ($config) {
                return new SmsproGateway($config);
            });
            $easySms->extend('twsms', function ($config) {
                return new TwsmsGateway($config);
            });
        }));

        exit;
    }
}
