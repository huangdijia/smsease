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
use Overtrue\EasySms\Support\Config;
use RuntimeException;

class AccessyouGateway extends Gateway
{
    use HasHttpRequest;

    const ENDPOINT_URL       = 'http://basic.accessyou-api.com/sms/sendsms-utf8-senderid.php';

    const ENDPOINT_QUERY_URL = 'https://q.accessyou-api.com/sms/check_accinfo.php';

    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        $data     = $message->getData($this);
        $signName = ! empty($data['sign_name']) ? $data['sign_name'] : $config->get('sign_name', '');

        unset($data['sign_name']);

        $msg = $message->getContent($this);

        if (! empty($msg) && mb_substr($msg, 0, 1) != '【' && ! empty($signName)) {
            $msg = '【' . $signName . '】' . $msg;
        }

        $params = [
            'msg'       => self::msgEncode($msg),
            'phone'     => $to->getUniversalNumber(),
            'accountno' => $config->get('account'),
            'pwd'       => $config->get('password'),
            'from'      => $config->get('from'),
            'size'      => $config->get('size', 'l'),
        ];

        $response = $this->get(self::ENDPOINT_URL, $params);
        $msgId    = trim($response->body());

        if (! is_numeric($msgId)) {
            throw new GatewayErrorException($msgId, 1, ['response' => $response]);
        }

        return [
            'msg_id' => $msgId,
        ];
    }

    public function getBalance(Config $config)
    {
        $params = [
            'accountno' => $config->get('account'),
            'user'      => $config->get('check_user'),
            'pwd'       => $config->get('check_password'),
        ];

        $response = $this->get(self::ENDPOINT_QUERY_URL, $params);

        if ($response->json('auth.auth_status', -1) != 100) {
            throw new RuntimeException($response->json('auth.auth_status_desc', ''), $response->json('auth.auth_status', 1));
        }

        return $response->json();
    }

    /**
     * Encode sms content.
     * @param string $str
     * @return null|string|string[]
     */
    private static function msgEncode($str = '')
    {
        return self::unicodeGet(self::convert(2, $str));
    }

    /**
     * Replace chars.
     * @param mixed $str
     * @return null|string|string[]
     */
    private static function unicodeGet($str)
    {
        $str = preg_replace('/&#/', '%26%23', $str);
        return preg_replace('/;/', '%3B', $str);
    }

    /**
     * Convert.
     * @param mixed $language
     * @param mixed $cell
     * @return string
     */
    private static function convert($language, $cell)
    {
        $str = '';
        preg_match_all("/[\x80-\xff]?./", $cell, $ar);

        switch ($language) {
            case 0: // 繁体中文
                foreach ($ar[0] as $v) {
                    $str .= '&#' . self::chineseUnicode(iconv('big5-hkscs', 'UTF-8', $v)) . ';';
                }
                return $str;
            // break;
            case 1: // 简体中文
                foreach ($ar[0] as $v) {
                    $str .= '&#' . self::chineseUnicode(iconv('gb2312', 'UTF-8', $v)) . ';';
                }
                return $str;
            // break;
            case 2: // 二进制编码
                $cell = self::utf8Unicode($cell);
                foreach ($cell as $v) {
                    $str .= '&#' . $v . ';';
                }
                return $str;
                // break;
        }

        return $str;
    }

    /**
     * encode unicode.
     * @param mixed $c
     * @return int
     */
    private static function chineseUnicode($c)
    {
        switch (strlen($c)) {
            case 1:
                return ord($c);
            case 2:
                $n = (ord($c[0]) & 0x3f) << 6;
                $n += ord($c[1]) & 0x3f;
                return $n;
            case 3:
                $n = (ord($c[0]) & 0x1f)  << 12;
                $n += (ord($c[1]) & 0x3f) << 6;
                $n += ord($c[2]) & 0x3f;
                return $n;
            case 4:
                $n = (ord($c[0]) & 0x0f)  << 18;
                $n += (ord($c[1]) & 0x3f) << 12;
                $n += (ord($c[2]) & 0x3f) << 6;
                $n += ord($c[3]) & 0x3f;
                return $n;
        }

        return 0;
    }

    /**
     * encode utf8 unicode.
     * @param mixed $str
     * @return array
     */
    private static function utf8Unicode($str)
    {
        $unicode    = [];
        $values     = [];
        $lookingFor = 1;

        for ($i = 0; $i < strlen($str); ++$i) {
            $thisValue = ord($str[$i]);

            if ($thisValue < 128) {
                $unicode[] = $thisValue;
            } else {
                if (count($values) == 0) {
                    $lookingFor = ($thisValue < 224) ? 2 : 3;
                }

                $values[] = $thisValue;

                if (count($values) == $lookingFor) {
                    $number = ($lookingFor == 3) ? (($values[0] % 16) * 4096) + (($values[1] % 64) * 64) + ($values[2] % 64) : (($values[0] % 32) * 64) + ($values[1] % 64);

                    $unicode[]  = $number;
                    $values     = [];
                    $lookingFor = 1;
                }
            }
        }

        return $unicode;
    }
}
