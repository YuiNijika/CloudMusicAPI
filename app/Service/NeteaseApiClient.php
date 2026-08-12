<?php

declare(strict_types=1);

namespace Anon\Service;

use Anon\Core\Facade\Config;
use Anon\Service\Netease\Crypto;

/**
 * 网易云 API
 */
class NeteaseApiClient
{
    private const DOMAIN = 'https://music.163.com';
    private const EAPI_DOMAIN = 'https://interfacepc.music.163.com';
    private const MODE_WEAPI = 'weapi';
    private const MODE_EAPI = 'eapi';
    private const MODE_LOCAL = 'local';

    // 统一 UA
    private const UA_PC =
        'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 ' .
        '(KHTML, like Gecko) Safari/537.36 Chrome/91.0.4472.164 NeteaseMusicDesktop/3.1.29.205117';

    /** 
     * eapi 请求体里的设备头
     * deviceId 动态生成——固定 id 多人共用会被网易云按设备维度风控，见 eapiHeader() 
     */
    private const EAPI_HEADER = [
        'osver' => 'Microsoft-Windows-10-Professional-build-19045-64bit',
        'os' => 'pc',
        'appver' => '3.1.17.204416',
        'versioncode' => '140',
        'mobilename' => '',
        'buildver' => '1700000000',
        'resolution' => '1920x1080',
        '__csrf' => '',
        'channel' => 'netease',
        'requestId' => '',
    ];

    /**
     * 接口表：route 名 => [上游 API 路径, 加密模式]。
     * {id} / {t} 占位符由 query 参数填充
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const ROUTES = [
        'song_url' => ['/api/song/enhance/player/url', self::MODE_EAPI],
        'song_detail' => ['/api/v3/song/detail', self::MODE_WEAPI],
        'lyric' => ['/api/song/lyric', self::MODE_EAPI],
        'search' => ['/api/search/get', self::MODE_EAPI],
        'playlist_detail' => ['/api/v6/playlist/detail', self::MODE_EAPI],
        'playlist_subscribe' => ['/api/playlist/{t}', self::MODE_EAPI],
        'playlist_tracks' => ['/api/playlist/manipulate/tracks', self::MODE_EAPI],
        'personalized' => ['/api/personalized/playlist', self::MODE_WEAPI],
        'recommend_songs' => ['/api/v3/discovery/recommend/songs', self::MODE_WEAPI],
        'login_qr_key' => ['/api/login/qrcode/unikey', self::MODE_EAPI],
        'login_qr_create' => ['', self::MODE_LOCAL],
        'login_qr_check' => ['/api/login/qrcode/client/login', self::MODE_EAPI],
        'captcha_sent' => ['/api/sms/captcha/sent', self::MODE_WEAPI],
        'login_cellphone' => ['/api/login/cellphone', self::MODE_WEAPI],
        'user_account' => ['/api/nuser/account/get', self::MODE_WEAPI],
        'user_playlist' => ['/api/user/playlist', self::MODE_WEAPI],
        'likelist' => ['/api/song/like/get', self::MODE_EAPI],
        'like' => ['/api/radio/like', self::MODE_WEAPI],
        'artists' => ['/api/v1/artist/{id}', self::MODE_WEAPI],
        'artist_album' => ['/api/artist/albums/{id}', self::MODE_WEAPI],
        'artist_mv' => ['/api/artist/mvs', self::MODE_WEAPI],
        'artist_desc' => ['/api/artist/introduction', self::MODE_WEAPI],
        'simi_artist' => ['/api/discovery/simiArtist', self::MODE_WEAPI],
        'album' => ['/api/v1/album/{id}', self::MODE_WEAPI],
        'album_sublist' => ['/api/album/sublist', self::MODE_WEAPI],
        'album_sub' => ['/api/album/{t}', self::MODE_WEAPI],
        'dj_hot' => ['/api/djradio/hot/v1', self::MODE_WEAPI],
        'dj_recommend' => ['/api/djradio/recommend/v1', self::MODE_WEAPI],
        'dj_detail' => ['/api/djradio/v2/get', self::MODE_WEAPI],
        'dj_program' => ['/api/dj/program/byradio', self::MODE_WEAPI],
        'dj_program_detail' => ['/api/dj/program/detail', self::MODE_WEAPI],
        'dj_sublist' => ['/api/djradio/get/subed', self::MODE_WEAPI],
        'dj_sub' => ['/api/djradio/{t}', self::MODE_WEAPI],
        'mv_url' => ['/api/song/enhance/play/mv/url', self::MODE_WEAPI],
        'mv_detail' => ['/api/v1/mv/detail', self::MODE_WEAPI],
    ];

    /**
     * @return array{status: int, body: array, cookies: string[]}
     */
    public function proxy(string $name, array $query): array
    {
        if ($name === 'login_qr_create') {
            return $this->loginQrCreate($query);
        }
        if (!isset(self::ROUTES[$name])) {
            throw new \RuntimeException("未知接口: {$name}");
        }

        [$uriTemplate, $mode] = self::ROUTES[$name];
        $uri = $this->fillUri($uriTemplate, $query);
        $data = $this->buildData($name, $query);
        $cookie = $this->cookieString($query);

        $response = match ($mode) {
            self::MODE_WEAPI => $this->requestWeapi($uri, $data, $cookie),
            self::MODE_EAPI => $this->requestEapi($uri, $data, $cookie),
            default => throw new \RuntimeException("未知加密模式: {$mode}"),
        };

        // playlist_tracks 512 = 重复提交歌单，重试一次
        if ($name === 'playlist_tracks' && ($response['body']['code'] ?? null) === 512) {
            $data['trackIds'] = json_encode(
                array_merge((array) $query['tracks'], (array) $query['tracks']),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            );
            $response = $this->requestEapi($uri, $data, $cookie);
        }

        if ($name === 'song_url') {
            $response = $this->sortSongUrl($response, $query);
        }

        // 登录类接口把上游 set-cookie 合并进 body.cookie（前端 auth-cookie 依赖它持久化凭证）
        if (in_array($name, ['login_qr_key', 'login_qr_check', 'login_cellphone'], true)
            && $response['cookies'] !== []
        ) {
            $response['body']['cookie'] = implode(';', $response['cookies']);
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $query
     * @return array{status: int, body: array, cookies: string[]}
     */
    private function requestWeapi(string $uri, array $data, string $cookie): array
    {
        $data['csrf_token'] = $this->csrfToken($cookie);
        $encrypted = Crypto::weapi($data);
        $url = self::DOMAIN . '/weapi/' . substr($uri, 5);

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Referer' => self::DOMAIN,
            'User-Agent' => self::UA_PC,
        ];
        if ($cookie !== '') {
            $headers['Cookie'] = $cookie;
        }

        return $this->post($url, http_build_query($encrypted), $headers);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $query
     * @return array{status: int, body: array, cookies: string[]}
     */
    private function requestEapi(string $uri, array $data, string $cookie): array
    {
        $data['header'] = $this->eapiHeader($cookie);
        $encrypted = Crypto::eapi($uri, $data);
        $url = self::EAPI_DOMAIN . '/eapi/' . substr($uri, 5);

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'User-Agent' => self::UA_PC,
        ];
        if ($cookie !== '') {
            $headers['Cookie'] = $cookie;
        }

        return $this->post($url, http_build_query($encrypted), $headers);
    }

    /**
     * @param array<string, string> $headers
     * @return array{status: int, body: array, cookies: string[]}
     */
    private function post(string $url, string $payload, array $headers): array
    {
        $curl = curl_init($url);
        $curlHeaders = [];
        foreach ($headers as $name => $value) {
            $curlHeaders[] = "{$name}: {$value}";
        }

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $curlHeaders);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        if (!(bool) Config::get('http.ssl_verify', true)) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        }

        $raw = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        curl_close($curl);

        if ($raw === false) {
            return ['status' => 502, 'body' => [], 'cookies' => []];
        }

        $headerStr = substr($raw, 0, $headerSize);
        $bodyStr = substr($raw, $headerSize);

        // 自己解析响应头：curl 不合并同名头，多条 Set-Cookie 全保留
        $cookies = [];
        foreach (explode("\r\n", $headerStr) as $line) {
            if (stripos($line, 'Set-Cookie:') !== 0) {
                continue;
            }
            $value = trim(substr($line, strlen('Set-Cookie:')));
            $cookies[] = preg_replace('/\s*Domain=[^;]*;?/', '', $value) ?? '';
        }

        $body = json_decode($bodyStr, true);
        if (!is_array($body)) {
            $body = [];
        }

        return [
            'status' => $status === 0 ? 502 : $status,
            'body' => $body,
            'cookies' => $cookies,
        ];
    }

    /**
     * 每个接口的请求体组装
     *
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function buildData(string $name, array $query): array
    {
        $int = static fn ($key, $default = 0) => (int) ($query[$key] ?? $default);
        $str = static fn ($key, $default = '') => trim((string) ($query[$key] ?? $default));

        return match ($name) {
            'song_url' => [
                'ids' => json_encode(explode(',', $str('id', $str('ids'))), JSON_UNESCAPED_SLASHES),
                'br' => $int('br', 999000),
            ],
            'song_detail' => [
                // 网易云要求 c 为 id 数组的 JSON 串（[{"id":1},{"id":2}]），与 CloudMusicAPI 一致
                'c' => '[' . implode(',', array_map(
                    static fn ($id) => '{"id":' . (int) $id . '}',
                    array_filter(explode(',', $str('ids', $str('id')))),
                )) . ']',
            ],
            'lyric' => ['id' => $str('id')],
            'search' => [
                's' => $str('keywords', $str('s')),
                'type' => $int('type', 1),
                'limit' => $int('limit', 30),
                'offset' => $int('offset', 0),
            ],
            'playlist_detail' => ['id' => $str('id'), 'n' => 100000, 's' => $int('s', 8)],
            'playlist_subscribe' => ['id' => $str('id')],
            'playlist_tracks' => [
                'op' => $str('op', 'add'),
                'pid' => $str('pid'),
                'trackIds' => json_encode(explode(',', $str('tracks')), JSON_UNESCAPED_SLASHES),
                'imme' => 'true',
            ],
            'personalized' => ['limit' => $int('limit', 30), 'total' => true, 'n' => 1000],
            // 可空参数不传给上游：网易云对缺省字段有默认行为，传空串反而报错
            'recommend_songs' => array_filter(['afresh' => $query['afresh'] ?? null], static fn ($v) => $v !== null),
            'login_qr_key' => ['type' => 3],
            'login_qr_check' => ['key' => $str('key'), 'type' => 3],
            'captcha_sent' => ['phone' => $str('phone'), 'ctcode' => $str('ctcode', '86')],
            'login_cellphone' => array_filter([
                'phone' => $str('phone'),
                'countrycode' => $str('countrycode') ?: null,
                'captcha' => $str('captcha') ?: null,
                'password' => $str('password') ?: null,
            ], static fn ($v) => $v !== null),
            'user_account' => [],
            'user_playlist' => [
                'uid' => $str('uid'),
                'limit' => $int('limit', 30),
                'offset' => $int('offset', 0),
            ],
            'likelist' => ['uid' => $str('uid')],
            'like' => ['trackId' => $str('id'), 'like' => $this->boolish($query['like'] ?? true)],
            'artists', 'album' => [],
            'artist_album' => ['limit' => $int('limit', 30), 'offset' => $int('offset', 0)],
            'artist_mv' => ['id' => $str('id'), 'limit' => $int('limit', 40), 'offset' => $int('offset', 0)],
            'artist_desc' => ['id' => $str('id')],
            'simi_artist' => ['artistid' => $str('id')],
            'album_sublist' => ['limit' => $int('limit', 25), 'offset' => $int('offset', 0)],
            'album_sub' => ['id' => $str('id')],
            'dj_hot' => ['limit' => $int('limit', 30), 'offset' => $int('offset', 0)],
            'dj_recommend' => [],
            'dj_detail' => ['id' => $str('rid', $str('id'))],
            'dj_program' => [
                'radioId' => $str('rid', $str('id')),
                'limit' => $int('limit', 30),
                'offset' => $int('offset', 0),
                'asc' => $this->boolish($query['asc'] ?? false),
            ],
            'dj_program_detail' => ['id' => $str('id')],
            'dj_sublist' => ['limit' => $int('limit', 30), 'offset' => $int('offset', 0)],
            'dj_sub' => ['id' => $str('rid', $str('id'))],
            'mv_url' => ['id' => $str('id'), 'r' => $int('r', 1080)],
            'mv_detail' => ['id' => $str('id')],
            default => [],
        };
    }

    private function fillUri(string $template, array $query): string
    {
        $id = trim((string) ($query['id'] ?? ''));
        $rid = trim((string) ($query['rid'] ?? $query['id'] ?? ''));
        $t = ($query['t'] ?? null) == 1 ? 'sub' : 'unsub';

        return str_replace(['{id}', '{rid}', '{t}'], [$id, $rid, $t], $template);
    }

    /**
     * @param array<string, mixed> $query
     */
    private function cookieString(array $query): string
    {
        return trim((string) ($query['cookie'] ?? ''));
    }

    private function csrfToken(string $cookie): string
    {
        if (preg_match('/(?:^|;\s*)__csrf=([^;]+)/', $cookie, $m)) {
            return $m[1];
        }

        return '';
    }

    /**
     * eapi 请求体 header：固定 pc 设备头，登录态 cookie 里的 MUSIC_U/A 补进 header 与 Cookie。
     *
     * @return array<string, string>
     */
    private function eapiHeader(string $cookie): array
    {
        $header = self::EAPI_HEADER;
        $header['deviceId'] = $this->deviceId($cookie);
        foreach (['MUSIC_U', 'MUSIC_A'] as $key) {
            if (preg_match('/(?:^|;\s*)' . $key . '=([^;]+)/', $cookie, $m)) {
                $header[$key] = $m[1];
            }
        }

        return $header;
    }

    /**
     * 登录设备标识：cookie 里 deviceId 优先
     * 前端持久化，保证扫码登录与后续 请求同一设备，登录态才绑定，否则用临时文件缓存随机值兜底
     * 固定 deviceId 被多人共用会触发网易云设备维度风控
     */
    private function deviceId(string $cookie): string
    {
        if (preg_match('/(?:^|;\s*)deviceId=([^;]+)/', $cookie, $m)) {
            $id = trim($m[1]);
            if ($id !== '') {
                return $id;
            }
        }

        $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'musicstorm-device-id';
        $cached = @file_get_contents($file);
        if (is_string($cached) && strlen($cached) === 36) {
            return $cached;
        }

        $id = self::randomUuid();
        @file_put_contents($file, $id);

        return $id;
    }

    private static function randomUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        $hex = bin2hex($data);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split($hex, 4));
    }

    /**
     * CloudMusicAPI 的 toBoolean：'false'/false → false，其余按真值。
     */
    private function boolish(mixed $value): bool
    {
        if ($value === 'false') {
            return false;
        }

        return (bool) $value;
    }

    /**
     * song_url 响应按请求 id 顺序重排
     *
     * @param array{status: int, body: array, cookies: string[]} $response
     * @param array<string, mixed> $query
     * @return array{status: int, body: array, cookies: string[]}
     */
    private function sortSongUrl(array $response, array $query): array
    {
        $ids = explode(',', trim((string) ($query['id'] ?? $query['ids'] ?? '')));
        $data = $response['body']['data'] ?? [];
        if (!is_array($data) || $ids === []) {
            return $response;
        }

        usort($data, static function ($a, $b) use ($ids): int {
            $ia = array_search((string) ($a['id'] ?? ''), $ids, true);
            $ib = array_search((string) ($b['id'] ?? ''), $ids, true);

            return ($ia === false ? PHP_INT_MAX : $ia) <=> ($ib === false ? PHP_INT_MAX : $ib);
        });
        $response['body']['data'] = $data;

        return $response;
    }

    /**
     * login_qr_create 是纯本地逻辑：qrurl 由前端 qrTextToDataUrl 生成二维码。
     *
     * @param array<string, mixed> $query
     * @return array{status: int, body: array, cookies: string[]}
     */
    private function loginQrCreate(array $query): array
    {
        $key = trim((string) ($query['key'] ?? ''));
        $qrurl = 'https://music.163.com/login?codekey=' . rawurlencode($key);

        return [
            'status' => 200,
            'body' => [
                'code' => 200,
                'data' => [
                    'qrurl' => $qrurl,
                    'qrimg' => '',
                ],
            ],
            'cookies' => [],
        ];
    }
}
