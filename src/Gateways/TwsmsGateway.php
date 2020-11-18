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
namespace Huangdijia\Smsease\Gateways;

use Huangdijia\Smsease\Traits\HasHttpRequest;
use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Exceptions\GatewayErrorException;
use Overtrue\EasySms\Gateways\Gateway;
use Overtrue\EasySms\Support\Config;

/**
 * @see https://www.twsms.com/dl/api_sms_doc_json.zip
 */
class TwsmsGateway extends Gateway
{
    use HasHttpRequest;

    const SUCCESS_CODE = '00000';

    // const ENDPOINT_URL = 'http://api.twsms.com/send.php';
    const ENDPOINT_URL = 'http://api.twsms.com/json/sms_send.php';

    protected static $errCodes = [
        '00000' => '完成',
        '00001' => '狀態尚未回復',
        '00010' => '帳號或密碼格式錯誤',
        '00011' => '帳號錯誤',
        '00012' => '密碼錯誤',
        '00020' => '通數不足',
        '00030' => 'IP無使用權限',
        '00040' => '帳號已停用',
        '00050' => 'sendtime格式錯誤',
        '00060' => 'expirytime格式錯誤',
        '00070' => 'popup格式錯誤',
        '00080' => 'mo格式錯誤',
        '00090' => 'longsms格式錯誤',
        '00100' => '手機號碼格式錯誤',
        '00110' => '沒有簡訊內容',
        '00120' => '長簡訊不支援國際門號',
        '00130' => '簡訊內容超過長度',
        '00140' => 'drurl格式錯誤',
        '00150' => 'sendtime預約的時間已經超過',
        '00300' => '找不到msgid',
        '00310' => '預約尚未送出',
        '00400' => '找不到 snumber 辨識碼',
        '00410' => '沒有任何 mo 資料',
        '00420' => 'smsQuery指定查詢的格式錯誤',
        '00430' => 'moQuery指定查詢的格式錯誤',
        '99998' => '資料處理異常，請重新發送',
        '99999' => '系統錯誤，請通知系統廠商',
    ];

    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        $data     = $message->getData($this);
        $signName = ! empty($data['sign_name']) ? $data['sign_name'] : $config->get('sign_name', '');

        unset($data['sign_name']);

        $msg = $message->getContent($this);

        if (! empty($msg) && mb_substr($msg, 0, 1) != '【' && ! empty($signName)) {
            $msg = '【' . $signName . '】' . $msg;
        }

        // $params = [
        //     'username' => $config->get('account'),
        //     'password' => $config->get('password'),
        //     'type' => $config->get('type') ?? 'now',
        //     'encoding' => $config->get('encoding') ?? 'big5',
        //     'vldtime' => $config->get('vldtime'),
        //     'dlvtime' => $config->get('dlvtime'),
        //     'mobile' => $to,
        //     'message' => iconv('utf-8', $config->get('encoding') ?? 'big5' . '//IGNORE', $msg),
        // ];

        $params = [
            'username' => $config->get('account'),
            'password' => $config->get('password'),
            'mobile'   => $to->getNumber(),
            'longsms'  => $config->get('longsms') ?? 'N',
            'message'  => urlencode($msg),
        ];

        $response = $this->post(self::ENDPOINT_URL, $params);

        $result = json_decode($response->body(), true) ?: [];
        $code   = $result['code'] ?? -1;

        if ($code != self::SUCCESS_CODE) {
            throw new GatewayErrorException(self::$errCodes[$code] ?? 'Unknown error', (int) $code, ['response' => $response]);
        }

        return $result;
    }
}
