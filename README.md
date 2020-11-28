# Smsease

[![Latest Stable Version](https://poser.pugx.org/huangdijia/smsease/version.png)](https://packagist.org/packages/huangdijia/smsease)
[![Total Downloads](https://poser.pugx.org/huangdijia/smsease/d/total.png)](https://packagist.org/packages/huangdijia/smsease)
[![GitHub license](https://img.shields.io/github/license/huangdijia/smsease)](https://github.com/huangdijia/smsease)

## Installation

~~~bash
composer require huangdijia/smsease
~~~

## Quickly Start

### PHP

* Call

~~~php
use Huangdijia\Smsease\Smsease;

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
            '__gateway__' => \Huangdijia\Smsease\Gateways\AccessyouGateway::class, // custom
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

$smsease = new Smsease($config);

$smsease->send(13188888888, [
    'content'  => 'Your verify code: 1234',
    'template' => 'SMS_001',
    'data' => [
        'code' => 1234
    ],
]);
~~~

### Hyperf

* Publish

~~~bash
php bin/hyperf.php vendor:publish "huangdijia/smsease"
~~~

* Call

~~~php
// Make by container
$container = \Hyperf\Utils\ApplicationContext::getcontainer();
$smsease = $container->get(\Huangdijia\Smsease\Smsease::class);
~~~

or

~~~php
// Make by annotation
class Foo
{
    @Inject
    @var \Huangdijia\Smsease\Smsease::class
    private $smsease;

    // ...
}
~~~

* Options

If you want using coroutine, add class_map setting into `config/autoload/annotations.php` of your project

~~~php
return [
    'scan' => [
        // ...
        'class_map' => [
            \GuzzleHttp\Client::class => BASE_PATH . '/vendor/huangdijia/smsease/class_map/GuzzleHttp/Client.php',
        ],
    ],
];
~~~

### Laravel

* Publish

~~~bash
php artisan vendor:publish --provider="Huangdijia\Smsease\SmseaseServiceProvider"
~~~

* Call

~~~php
$smsease = app(\Huangdijia\Smsease\Smsease::class);
// or
$smsease = app('smsease');
~~~

## Link

[overtrue/easy-sms](https://github.com/overtrue/easy-sms)
