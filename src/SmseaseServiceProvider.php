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
use Illuminate\Support\ServiceProvider;

class SmseaseServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        $this->configure();

        $this->app->singleton(Smsease::class, function ($app) {
            return tap(new Smsease($app['config']->get('smsease')), function ($smsease) use ($app) {
                /** @var Smsease $smsease */
                $gateways = $app['config']->get('smsease.gateways', []);

                foreach ($gateways as $name => $config) {
                    $gatewayClass = $config['__gateway__'] ?? '';

                    if (! class_exists($config['__gateway__']) || ! in_array(GatewayInterface::class, class_implements($config['__gateway__']))) {
                        continue;
                    }

                    $smsease->extend($name, function ($config) use ($gatewayClass) {
                        return new $gatewayClass($config);
                    });
                }
            });
        });

        $this->app->alias(EasySms::class, 'easysms');
    }

    public function configure()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/easysms.php', 'easysms');

        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/easysms.php' => $this->app->basePath('config/easysms.php')]);
        }
    }

    public function provides()
    {
        return [
            'easysms',
        ];
    }
}
