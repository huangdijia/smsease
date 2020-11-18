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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Overtrue\EasySms\Contracts\GatewayInterface;
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
        $container = \Hyperf\Utils\ApplicationContext::getContainer();
        /** @var ConfigInterface $config */
        $config = $container->get(ConfigInterface::class);

        $container->set(EasySms::class, tap(new EasySms($config->get('easysms', [])), function ($easySms) use ($config) {
            /** @var \Overtrue\EasySms\EasySms $easySms */
            $gateways = $config->get('easysms.gateways', []);

            foreach ($gateways as $name => $config) {
                $gatewayClass = $config['__gateway__'] ?? '';

                if (! class_exists($gatewayClass) || ! in_array(GatewayInterface::class, class_implements($gatewayClass))) {
                    continue;
                }

                $easySms->extend($name, function ($config) use ($gatewayClass) {
                    return new $gatewayClass($config);
                });
            }
        }));
    }
}
