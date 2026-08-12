<?php

declare(strict_types=1);

namespace Anon\Service\Netease;

/**
 * 网易云 weapi / eapi 请求加密（移植 CloudMusicAPI util/crypto.js）。
 *
 * weapi：文本先 AES-128-CBC（固定 presetKey），再用随机 16 位 base62 key
 *       做第二层 CBC；随机 key 反转后经 RSA 无填充加密（forge NONE 语义 =
 *       大整数幂模，前补零到 128 字节数值不变，openssl NO_PADDING 可等价复刻）。
 * eapi：明文 = "{url}-36cd479b6b5-{text}-36cd479b6b5-{md5}"，AES-128-ECB
 *       输出大写 hex。
 */
final class Crypto
{
    private const IV = '0102030405060708';
    private const PRESET_KEY = '0CoJUm6Qyw8W8jud';
    private const EAPI_KEY = 'e82ckenh8dichen8';
    private const BASE62 = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    private const PUBLIC_KEY = <<<'PEM'
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDgtQn2JZ34ZC28NWYpAUd98iZ37BUrX/aKzmFbt7clFSs6sXqHauqKWqdtLkF2KexO40H1YTX8z2lSgBBOAxLsvaklV8k4cBFK9snQXE9/DDaFt6Rr7iVZMldczhC0JNgTz+SHXT6CBHuX3e9SdB1Ua44oncaTWz7OBGLbCiK45wIDAQAB
-----END PUBLIC KEY-----
PEM;

    /**
     * @param array<string, mixed> $data
     * @return array{params: string, encSecKey: string}
     */
    public static function weapi(array $data): array
    {
        $text = self::encode($data);
        $secretKey = self::randomKey();

        return [
            'params' => self::aesCbc(self::aesCbc($text, self::PRESET_KEY), $secretKey),
            'encSecKey' => self::rsaEncrypt(strrev($secretKey)),
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{params: string}
     */
    public static function eapi(string $url, array $data): array
    {
        $text = self::encode($data);
        $digest = md5("nobody{$url}use{$text}md5forencrypt");
        $payload = "{$url}-36cd479b6b5-{$text}-36cd479b6b5-{$digest}";

        return ['params' => self::aesEcbHex($payload)];
    }

    /**
     * 与 CryptoJS JSON.stringify 对齐：空对象必须编码为 {}（PHP 空数组默认是 []）。
     */
    private static function encode(array $data): string
    {
        return json_encode(
            $data === [] ? new \stdClass() : $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        ) ?: '{}';
    }

    private static function aesCbc(string $text, string $key): string
    {
        $cipher = openssl_encrypt($text, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, self::IV);

        return base64_encode($cipher ?: '');
    }

    private static function aesEcbHex(string $text): string
    {
        $cipher = openssl_encrypt($text, 'AES-128-ECB', self::EAPI_KEY, OPENSSL_RAW_DATA);

        return strtoupper(bin2hex($cipher ?: ''));
    }

    private static function rsaEncrypt(string $secret): string
    {
        $block = str_pad($secret, 128, "\0", STR_PAD_LEFT);
        openssl_public_encrypt($block, $encrypted, self::PUBLIC_KEY, OPENSSL_NO_PADDING);

        return bin2hex($encrypted ?: '');
    }

    private static function randomKey(): string
    {
        $key = '';
        for ($i = 0; $i < 16; $i++) {
            $key .= self::BASE62[random_int(0, 61)];
        }

        return $key;
    }
}
