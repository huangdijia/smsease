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

use Illuminate\Support\ServiceProvider;
use Overtrue\EasySms\EasySms;

class SmseaseServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        $this->configure();

        $this->app->singleton(EasySms::class, function ($app) {
            return new EasySms($app['config']->get('easysms'));
        });

        $this->app->alias(EasySms::class, 'easysms');
    }

    public function configure()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/easysms.php', 'easysms');

        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/easysms.php' => $this->app->basePath('config/easysms.php')]);
            $this->publishes([__DIR__ . '/../routes/easysms.php' => $this->app->basePath('routes/easysms.php')]);
        }
    }

    public function provides()
    {
        return [
            'easysms',
        ];
    }
}
