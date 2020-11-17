# Smsease

## Installation

~~~bash
composer require huangdijia/smsease
~~~

## Quickly Start

### PHP

* Call

~~~php
use Overtrue\EasySms\EasySms;

$config = [
    // HTTP request timeout
    'timeout' => 5.0,

    // Default send options
    'default' => [
        // Gateway strategy, default: OrderStrategy
        'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

        // gateway using
        'gateways' => [
            'accessyou', 'aliyun',
        ],
    ],
    // Available gateways
    'gateways' => [
        'accessyou' => [
            'account' => 'account',
            'password' => 'password',
        ],
        'aliyun' => [
            'access_key_id' => '',
            'access_key_secret' => '',
            'sign_name' => '',
        ],
        //...
    ],
];

$easysms = new EasySms($config);

$easysms->send(13188888888, [
    'content'  => 'Your verify code: 1234',
    'template' => 'SMS_001',
    'data' => [
        'code' => 1234
    ],
]);
~~~

* Extends

~~~php
use Huangdijia\Smsease\Gateways\AccessyouGateway;
use Huangdijia\Smsease\Gateways\MitakeGateway;
use Huangdijia\Smsease\Gateways\MxtongGateway;
use Huangdijia\Smsease\Gateways\SmsproGateway;
use Huangdijia\Smsease\Gateways\TwsmsGateway;

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
~~~

### Hyperf

* Publish

~~~bash
php bin/hyperf.php vendor:publish "huangdijia/smsease"
~~~

* Call

~~~php
$container = \Hyperf\Utils\ApplicationContext::getcontainer();
$easysms = $container->get(\Overtrue\EasySms\EasySms::class);
~~~

### Laravel

* Publish

~~~bash
php artisan vendor:publish --provider="Huangdijia\Smsease\SmseaseServiceProvider"
~~~

* Call

~~~php
$easysms = app(\Overtrue\EasySms\EasySms::class);
~~~

## Link

[overtrue/easy-sms](https://github.com/overtrue/easy-sms)
