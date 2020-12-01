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

use Huangdijia\Smsease\Smsease;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Overtrue\EasySms\Contracts\GatewayInterface;

class BootApplicationListener implements ListenerInterface
{
    /**
     * @Inject
     * @var \Hyperf\Di\Container
     */
    protected $container;

    /**
     * @Inject
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

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
        $this->container->set(Smsease::class, tap(new Smsease($this->config->get('smsease', [])), function ($smsease) {
            /** @var Smsease $smsease */
            $gateways = $this->config->get('smsease.gateways', []);

            foreach ($gateways as $name => $config) {
                $gatewayClass = $config['__gateway__'] ?? '';

                if (! class_exists($gatewayClass) || ! in_array(GatewayInterface::class, class_implements($gatewayClass))) {
                    $this->logger->warning(sprintf('[smsease] Gateway %s[%s] is invalid gateway!', $gatewayClass, $name));
                    continue;
                }

                $smsease->extend($name, function ($config) use ($gatewayClass) {
                    return new $gatewayClass($config);
                });

                $this->logger->debug(sprintf('[smsease] Gateway %s[%s] registered by %s.', $gatewayClass, $name, __CLASS__));
            }
        }));
    }
}
