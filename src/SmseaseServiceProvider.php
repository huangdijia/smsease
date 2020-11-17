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

use Huangdijia\Smsease\Gateways\AccessyouGateway;
use Huangdijia\Smsease\Gateways\AliyunGateway;
use Huangdijia\Smsease\Gateways\MitakeGateway;
use Huangdijia\Smsease\Gateways\MxtongGateway;
use Huangdijia\Smsease\Gateways\SmsproGateway;
use Huangdijia\Smsease\Gateways\TwsmsGateway;
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
            return tap(new EasySms($app['config']->get('easysms')), function ($easySms) {
                /* @var \Overtrue\EasySms\EasySms $easySms */
                $easySms->extend('accessyou', function ($config) {
                    return new AccessyouGateway($config);
                });
                $easySms->extend('aliyun', function ($config) {
                    return new AliyunGateway($config);
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
