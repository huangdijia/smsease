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
return [
    // HTTP 请求的超时时间（秒）
    'timeout' => 5.0,

    // 默认发送配置
    'default' => [
        // 网关调用策略，默认：顺序调用
        'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

        // 默认可用的发送网关
        'gateways' => [
            'aliyun',
        ],
    ],
    // 可用的网关配置
    'gateways' => [
        'aliyun' => [
            '__gateway__'       => \Huangdijia\Smsease\Gateways\AliyunGateway::class,
            'access_key_id'     => env('ALIYUN_ACCESS_KEY', ''),
            'access_key_secret' => env('ALIYUN_ACCESS_SECRET', ''),
            'sign_name'         => '',
        ],
        'accessyou' => [
            '__gateway__'    => \Huangdijia\Smsease\Gateways\AccessyouGateway::class,
            'account'        => env('ACCESSYOU_ACCOUNT', ''),
            'password'       => env('ACCESSYOU_PASSWORD', ''),
            'check_user'     => env('ACCESSYOU_CHECK_USER', ''),
            'check_password' => env('ACCESSYOU_CHECK_PASSWORD', ''),
            'sign_name'      => '',
        ],
        'mitake' => [
            '__gateway__' => \Huangdijia\Smsease\Gateways\MitakeGateway::class,
            'username'    => env('MITAKE_USERNAME', ''),
            'password'    => env('MITAKE_PASSWORD', ''),
            'encoding'    => env('MITAKE_ENCODING', 'big5'),
            'sign_name'   => '',
        ],
        'mxtong' => [
            '__gateway__'     => \Huangdijia\Smsease\Gateways\MxtongGateway::class,
            'user_id'         => env('MXTONG_USER_ID', ''),
            'account'         => env('MXTONG_ACCOUNT', ''),
            'password'        => env('MXTONG_PASSWORD', ''),
            'send_type'       => 1,
            'post_fix_number' => 1,
            'sign_name'       => '',
        ],
        'smspro' => [
            '__gateway__' => \Huangdijia\Smsease\Gateways\SmsproGateway::class,
            'username'    => env('SMSPRO_USERNAME', ''),
            'password'    => env('SMSPRO_PASSWORD', ''),
            'sender'      => env('SMSPRO_SENDER', ''),
            'sign_name'   => '',
        ],
        'twsms' => [
            '__gateway__' => \Huangdijia\Smsease\Gateways\TwsmsGateway::class,
            'account'     => env('TWSMS_ACCOUNT', ''),
            'password'    => env('TWSMS_PASSWORD', ''),
            'type'        => env('TWSMS_TYPE', 'now'),
            'encoding'    => env('TWSMS_ENCODING', 'big5'),
            'vldtime'     => env('TWSMS_VLDTIME', ''),
            'dlvtime'     => env('TWSMS_DLVTIME', ''),
            'sign_name'   => '',
        ],
    ],
];
