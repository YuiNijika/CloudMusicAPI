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

    // weapi 是网页端接口，对齐 Node 版 chooseUserAgent('weapi')：浏览器 UA
    private const UA_WEB =
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 ' .
        '(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 Edg/124.0.0.0';

    /** 
     * eapi 请求体里的设备头静态字段
     * deviceId/buildver/__csrf/requestId 动态生成（见 eapiHeader），固定值会被风控
     */
    private const EAPI_HEADER = [
        'osver' => 'Microsoft-Windows-10-Professional-build-19045-64bit',
        'os' => 'pc',
        'appver' => '3.1.17.204416',
        'versioncode' => '140',
        'mobilename' => '',
        'resolution' => '1920x1080',
        'channel' => 'netease',
    ];

    /** 收藏歌单反作弊 token，对齐 Node config.json 的 APP_CONF.checkToken */
    private const CHECK_TOKEN = '9ca17ae2e6ffcda170e2e6ee8af14fbabdb988f225b3868eb2c15a879b9a83d274a790ac8ff54a97b889d5d42af0feaec3b92af58cff99c470a7eafd88f75e839a9ea7c14e909da883e83fb692a3abdb6b92adee9e';

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
        'search' => ['/api/cloudsearch/pc', self::MODE_EAPI],
        'playlist_detail' => ['/api/v6/playlist/detail', self::MODE_EAPI],
        'playlist_subscribe' => ['/api/playlist/{t}', self::MODE_EAPI],
        'playlist_tracks' => ['/api/playlist/manipulate/tracks', self::MODE_EAPI],
        'personalized' => ['/api/personalized/playlist', self::MODE_WEAPI],
        'recommend_songs' => ['/api/v3/discovery/recommend/songs', self::MODE_WEAPI],
        'login_qr_key' => ['/api/login/qrcode/unikey', self::MODE_EAPI],
        'login_qr_create' => ['', self::MODE_LOCAL],
        'login_qr_check' => ['/api/login/qrcode/client/login', self::MODE_EAPI],
        'captcha_sent' => ['/api/sms/captcha/sent', self::MODE_WEAPI],
        'login_cellphone' => ['/api/w/login/cellphone', self::MODE_WEAPI],
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
        'song_like_check' => ['/api/song/like/check', self::MODE_EAPI],
        'lyric_new' => ['/api/song/lyric/v1', self::MODE_EAPI],
        'banner' => ['/api/v2/banner/get', self::MODE_EAPI],
        'personalized_newsong' => ['/api/personalized/newsong', self::MODE_WEAPI],
        'personalized_djprogram' => ['/api/personalized/djprogram', self::MODE_WEAPI],
        'personalized_mv' => ['/api/personalized/mv', self::MODE_WEAPI],
        'toplist' => ['/api/toplist', self::MODE_EAPI],
        'toplist_detail' => ['/api/toplist/detail', self::MODE_WEAPI],
        'top_song' => ['/api/v1/discovery/new/songs', self::MODE_WEAPI],
        'top_album' => ['/api/discovery/new/albums/area', self::MODE_WEAPI],
        'top_artists' => ['/api/artist/top', self::MODE_WEAPI],
        'top_mv' => ['/api/mv/toplist', self::MODE_WEAPI],
        'top_playlist' => ['/api/playlist/list', self::MODE_WEAPI],
        'search_hot' => ['/api/search/hot', self::MODE_EAPI],
        'search_suggest' => ['/api/search/suggest/web', self::MODE_WEAPI],
        'simi_song' => ['/api/v1/discovery/simiSong', self::MODE_WEAPI],
        'simi_playlist' => ['/api/discovery/simiPlaylist', self::MODE_WEAPI],
        'simi_mv' => ['/api/discovery/simiMV', self::MODE_WEAPI],
        'related_allvideo' => ['/api/cloudvideo/v1/allvideo/rcmd', self::MODE_WEAPI],
        'artist_songs' => ['/api/v1/artist/songs', self::MODE_EAPI],
        'artist_top_song' => ['/api/artist/top/song', self::MODE_WEAPI],
        'artist_sublist' => ['/api/artist/sublist', self::MODE_WEAPI],
        'artist_sub' => ['/api/artist/{t}', self::MODE_WEAPI],
        'mv_all' => ['/api/mv/all', self::MODE_EAPI],
        'mv_first' => ['/api/mv/first', self::MODE_EAPI],
        'mv_sublist' => ['/api/cloudvideo/allvideo/sublist', self::MODE_WEAPI],
        'mv_sub' => ['/api/mv/{t}', self::MODE_WEAPI],
        'personal_fm' => ['/api/v1/radio/get', self::MODE_WEAPI],
        'playlist_create' => ['/api/playlist/create', self::MODE_WEAPI],
        'playlist_delete' => ['/api/playlist/remove', self::MODE_WEAPI],
        'playlist_update_name' => ['/api/playlist/update/name', self::MODE_WEAPI],
        'login_status' => ['/api/w/nuser/account/get', self::MODE_WEAPI],
        'logout' => ['/api/logout', self::MODE_EAPI],
        'user_detail' => ['/api/v1/user/detail/{uid}', self::MODE_WEAPI],
        'user_subcount' => ['/api/subcount', self::MODE_WEAPI],
        'user_level' => ['/api/user/level', self::MODE_WEAPI],
        'user_record' => ['/api/v1/play/record', self::MODE_WEAPI],
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
            $response = $this->forceHttpsMedia($response);
        }

        // 登录类接口把上游 set-cookie 合并进 body.cookie，前端 auth-cookie 依赖它持久化凭证
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
        $jar = $this->processCookie($cookie, $uri);
        $data['csrf_token'] = $jar['__csrf'] ?? '';
        $encrypted = Crypto::weapi($data);
        $url = self::DOMAIN . '/weapi/' . substr($uri, 5);

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Referer' => self::DOMAIN,
            'User-Agent' => self::UA_WEB,
            'Cookie' => $this->cookieHeader($jar),
        ];

        return $this->post($url, http_build_query($encrypted), $headers);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $query
     * @return array{status: int, body: array, cookies: string[]}
     */
    private function requestEapi(string $uri, array $data, string $cookie): array
    {
        $jar = $this->processCookie($cookie, $uri);
        $data['header'] = $this->eapiHeader($jar);
        $encrypted = Crypto::eapi($uri, $data);
        $url = self::EAPI_DOMAIN . '/eapi/' . substr($uri, 5);

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'User-Agent' => self::UA_PC,
            'Cookie' => $this->eapiHeaderCookie($data['header']),
        ];

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
                // 网易云要求 c 为 id 数组的 JSON 串，与 CloudMusicAPI Node 版一致
                'c' => '[' . implode(',', array_map(
                    static fn ($id) => '{"id":' . (int) $id . '}',
                    array_filter(explode(',', $str('ids', $str('id')))),
                )) . ']',
            ],
            // 缺 tv/lv/rv/kv/_nmclfl 时网易云只回基础歌词甚至空 lrc——版本号 -1 请求全版本
            'lyric' => [
                'id' => $str('id'),
                'tv' => -1,
                'lv' => -1,
                'rv' => -1,
                'kv' => -1,
                '_nmclfl' => 1,
            ],
            'search' => [
                's' => $str('keywords', $str('s')),
                'type' => $int('type', 1),
                'limit' => $int('limit', 30),
                'offset' => $int('offset', 0),
                'total' => true,
            ],
            'playlist_detail' => ['id' => $str('id'), 'n' => 100000, 's' => $int('s', 8)],
            // 收藏（t=1）带反作弊 token，对齐 Node playlist_subscribe.js
            'playlist_subscribe' => array_filter([
                'id' => $str('id'),
                'checkToken' => ($query['t'] ?? null) == 1 ? self::CHECK_TOKEN : null,
            ], static fn ($v) => $v !== null),
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
            // secrete 必填，字段名是 cellphone 不是 phone，对齐 Node captcha_sent.js
            'captcha_sent' => ['ctcode' => $str('ctcode', '86'), 'secrete' => 'music_middleuser_pclogin', 'cellphone' => $str('phone')],
            'login_cellphone' => $this->loginCellphoneData($query),
            'user_account' => [],
            'user_playlist' => [
                'uid' => $str('uid'),
                'limit' => $int('limit', 30),
                'offset' => $int('offset', 0),
                'includeVideo' => true,
            ],
            'likelist' => ['uid' => $str('uid')],
            // alg/time 是红心接口必填，缺失会被网易云判定非法请求直接拒绝
            'like' => [
                'alg' => 'itembased',
                'trackId' => $str('id'),
                'like' => $this->boolish($query['like'] ?? true),
                'time' => '3',
            ],
            'artists', 'album' => [],
            'artist_album' => ['limit' => $int('limit', 30), 'offset' => $int('offset', 0), 'total' => true],
            // 上游字段是 artistId 不是 id，缺 total 拿不到分页总数
            'artist_mv' => ['artistId' => $str('id'), 'limit' => $int('limit', 40), 'offset' => $int('offset', 0), 'total' => true],
            'artist_desc' => ['id' => $str('id')],
            'simi_artist' => ['artistid' => $str('id')],
            'album_sublist' => ['limit' => $int('limit', 25), 'offset' => $int('offset', 0), 'total' => true],
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
            'dj_sublist' => ['limit' => $int('limit', 30), 'offset' => $int('offset', 0), 'total' => true],
            'dj_sub' => ['id' => $str('rid', $str('id'))],
            'mv_url' => ['id' => $str('id'), 'r' => $int('r', 1080)],
            'mv_detail' => ['id' => $str('id')],
            'song_like_check' => ['trackIds' => $str('ids')],
            'lyric_new' => [
                'id' => $str('id'),
                'cp' => false,
                'tv' => 0,
                'lv' => 0,
                'rv' => 0,
                'kv' => 0,
                'yv' => 0,
                'ytv' => 0,
                'yrv' => 0,
            ],
            'banner' => ['clientType' => self::bannerClientType($query['type'] ?? 0)],
            'personalized_newsong' => ['type' => 'recommend', 'limit' => $int('limit', 10), 'areaId' => $int('areaId', 0)],
            'personalized_djprogram' => [],
            'personalized_mv' => [],
            'toplist' => [],
            'toplist_detail' => [],
            'top_song' => ['areaId' => $int('type', 0), 'total' => true],
            'top_album' => [
                'area' => $str('area', 'ALL'),
                'limit' => $int('limit', 50),
                'offset' => $int('offset', 0),
                'type' => $str('type', 'new'),
                'year' => $int('year', (int) date('Y')),
                'month' => $int('month', (int) date('n')),
                'total' => false,
                'rcmd' => true,
            ],
            'top_artists' => ['limit' => $int('limit', 50), 'offset' => $int('offset', 0), 'total' => true],
            'top_mv' => ['area' => $str('area', ''), 'limit' => $int('limit', 30), 'offset' => $int('offset', 0), 'total' => true],
            'top_playlist' => ['cat' => $str('cat', '全部'), 'order' => $str('order', 'hot'), 'limit' => $int('limit', 50), 'offset' => $int('offset', 0), 'total' => true],
            'search_hot' => ['type' => 1111],
            'search_suggest' => ['s' => $str('keywords', $str('s'))],
            'simi_song' => ['songid' => $str('id'), 'limit' => $int('limit', 50), 'offset' => $int('offset', 0)],
            'simi_playlist' => ['songid' => $str('id'), 'limit' => $int('limit', 50), 'offset' => $int('offset', 0)],
            'simi_mv' => ['mvid' => $str('mvid', $str('id'))],
            'related_allvideo' => ['id' => $str('id'), 'type' => ctype_digit((string) ($query['id'] ?? '')) ? 0 : 1],
            'artist_songs' => [
                'id' => $str('id'),
                'private_cloud' => 'true',
                'work_type' => 1,
                'order' => $str('order', 'hot'),
                'offset' => $int('offset', 0),
                'limit' => $int('limit', 100),
            ],
            'artist_top_song' => ['id' => $str('id')],
            'artist_sublist' => ['limit' => $int('limit', 25), 'offset' => $int('offset', 0), 'total' => true],
            'artist_sub' => ['artistId' => $str('id'), 'artistIds' => '[' . $str('id') . ']'],
            'mv_all' => [
                'tags' => json_encode(
                    [
                        '地区' => $str('area', '全部'),
                        '类型' => $str('type', '全部'),
                        '排序' => $str('order', '上升最快'),
                    ],
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                ),
                'offset' => $int('offset', 0),
                'total' => 'true',
                'limit' => $int('limit', 30),
            ],
            'mv_first' => ['area' => $str('area', ''), 'limit' => $int('limit', 30), 'total' => true],
            'mv_sublist' => ['limit' => $int('limit', 25), 'offset' => $int('offset', 0), 'total' => true],
            'mv_sub' => ['mvId' => $str('mvid', $str('id')), 'mvIds' => '["' . $str('mvid', $str('id')) . '"]'],
            'personal_fm' => [],
            'playlist_create' => ['name' => $str('name'), 'privacy' => $str('privacy', '0'), 'type' => $str('type', 'NORMAL')],
            'playlist_delete' => ['ids' => '[' . $str('id') . ']'],
            'playlist_update_name' => ['id' => $str('id'), 'name' => $str('name')],
            'login_status' => [],
            'logout' => [],
            'user_detail' => [],
            'user_subcount' => [],
            'user_level' => [],
            'user_record' => ['uid' => $str('uid'), 'type' => $int('type', 0)],
            default => [],
        };
    }

    private function fillUri(string $template, array $query): string
    {
        $id = trim((string) ($query['id'] ?? ''));
        $rid = trim((string) ($query['rid'] ?? $query['id'] ?? ''));
        $uid = trim((string) ($query['uid'] ?? ''));
        // playlist 的 {t} 是 subscribe/unsubscribe，其余 sub 接口是 sub/unsub
        $t = str_contains($template, '/playlist/')
            ? (($query['t'] ?? null) == 1 ? 'subscribe' : 'unsubscribe')
            : (($query['t'] ?? null) == 1 ? 'sub' : 'unsub');

        return str_replace(['{id}', '{rid}', '{uid}', '{t}'], [$id, $rid, $uid, $t], $template);
    }

    /**
     * 手机号登录请求体，对齐 Node login_cellphone.js。
     * type/https/remember/secureCaptcha 是风控关键字段，缺任一会被网易云判定异常请求；
     * countrycode 兼容前端 auth-phone.ts 只传 ctcode 的情况，captcha 优先、否则走 md5(password)。
     *
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function loginCellphoneData(array $query): array
    {
        $str = static fn (string $key, string $default = '') => trim((string) ($query[$key] ?? $default));

        $countrycode = $str('countrycode');
        if ($countrycode === '') {
            $countrycode = $str('ctcode', '86');
        }

        $data = [
            'type' => '1',
            'https' => 'true',
            'phone' => $str('phone'),
            'countrycode' => $countrycode,
            'remember' => 'true',
            'secureCaptcha' => $str('sca', ''),
        ];

        $captcha = $str('captcha');
        if ($captcha !== '') {
            $data['captcha'] = $captcha;
        } else {
            $md5Password = $str('md5_password');
            $data['password'] = $md5Password !== '' ? $md5Password : md5($str('password'));
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $query
     */
    private function cookieString(array $query): string
    {
        return trim((string) ($query['cookie'] ?? ''));
    }

    private function cookieToRecord(string $cookie): array
    {
        $out = [];
        if ($cookie === '') {
            return $out;
        }
        foreach (explode(';', $cookie) as $part) {
            $idx = strpos($part, '=');
            if ($idx === false || $idx <= 0) {
                continue;
            }
            $key = trim(substr($part, 0, $idx));
            $value = trim(substr($part, $idx + 1));
            if ($key !== '') {
                $out[$key] = $value;
            }
        }

        return $out;
    }

    /**
     * 补全设备指纹 cookie，对齐 Node processCookieObject：
     * 网易云风控校验 _ntes_nuid/NMTID/WNMCID/osver/appver 等设备字段，
     * 只透传前端的 deviceId + MUSIC_U 会被判环境风险（-460）。
     * 登录接口不加 NMTID，其余接口补上。
     */
    private function processCookie(string $cookie, string $uri): array
    {
        $jar = $this->cookieToRecord($cookie);
        $nuid = bin2hex(random_bytes(16));

        $jar['__remember_me'] = $jar['__remember_me'] ?? 'true';
        $jar['ntes_kaola_ad'] = $jar['ntes_kaola_ad'] ?? '1';
        $jar['_ntes_nuid'] = $jar['_ntes_nuid'] ?? $nuid;
        $jar['_ntes_nnid'] = $jar['_ntes_nnid'] ?? ($jar['_ntes_nuid'] . ',' . (string) (int) (microtime(true) * 1000));
        $jar['WNMCID'] = $jar['WNMCID'] ?? self::randomWnmcid();
        $jar['WEVNSM'] = $jar['WEVNSM'] ?? '1.0.0';
        $jar['osver'] = $jar['osver'] ?? 'Microsoft-Windows-10-Professional-build-19045-64bit';
        $jar['os'] = $jar['os'] ?? 'pc';
        $jar['appver'] = $jar['appver'] ?? '3.1.17.204416';
        $jar['channel'] = $jar['channel'] ?? 'netease';
        $jar['deviceId'] = $this->deviceId($jar);

        if (!str_contains($uri, 'login') && !isset($jar['NMTID'])) {
            $jar['NMTID'] = bin2hex(random_bytes(16));
        }

        return $jar;
    }

    private function cookieHeader(array $jar): string
    {
        $parts = [];
        foreach ($jar as $key => $value) {
            $parts[] = $key . '=' . $value;
        }

        return implode('; ', $parts);
    }

    /**
     * eapi 的 Cookie 头复用 header 同字段（URL 编码），对齐 Node createHeaderCookie。
     */
    private function eapiHeaderCookie(array $header): string
    {
        $parts = [];
        foreach ($header as $key => $value) {
            $parts[] = rawurlencode($key) . '=' . rawurlencode((string) $value);
        }

        return implode('; ', $parts);
    }

    private static function randomWnmcid(): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $s = '';
        for ($i = 0; $i < 6; $i++) {
            $s .= $chars[random_int(0, 25)];
        }

        return $s . '.' . (string) (int) (microtime(true) * 1000) . '.01.0';
    }

    /**
     * eapi 请求体 header：固定 pc 设备头，登录态 cookie 里的 MUSIC_U/A 补进 header 与 Cookie。
     *
     * @return array<string, string>
     */
    private function eapiHeader(array $jar): array
    {
        $header = self::EAPI_HEADER;
        $header['deviceId'] = $jar['deviceId'];
        $header['buildver'] = substr((string) time(), 0, 10);
        $header['__csrf'] = $jar['__csrf'] ?? '';
        $header['requestId'] = (string) (int) (microtime(true) * 1000) . '_' . str_pad((string) random_int(0, 999), 4, '0', STR_PAD_LEFT);
        foreach (['MUSIC_U', 'MUSIC_A'] as $key) {
            if (($jar[$key] ?? '') !== '') {
                $header[$key] = $jar[$key];
            }
        }

        return $header;
    }

    /**
     * 登录设备标识：cookie 里合法 deviceId（52 位大写 hex）优先，前端持久化保证
     * 扫码登录与后续请求同一设备，登录态才绑定；否则用临时文件缓存兜底。
     * UUID 等旧格式会被网易云判设备异常，强制替换。
     */
    private function deviceId(array $jar): string
    {
        $id = trim((string) ($jar['deviceId'] ?? ''));
        if (preg_match('/^[0-9A-F]{52}$/i', $id)) {
            return strtoupper($id);
        }

        $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'musicstorm-device-id';
        $cached = @file_get_contents($file);
        if (is_string($cached) && preg_match('/^[0-9A-F]{52}$/', $cached)) {
            return $cached;
        }

        $id = self::generateDeviceId();
        @file_put_contents($file, $id);

        return $id;
    }

    private static function generateDeviceId(): string
    {
        return strtoupper(bin2hex(random_bytes(26)));
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
     * banner 的 clientType：0 pc / 1 android / 2 iphone / 3 ipad，缺省 pc。
     */
    private static function bannerClientType(mixed $type): string
    {
        return match ((int) $type) {
            1 => 'android',
            2 => 'iphone',
            3 => 'ipad',
            default => 'pc',
        };
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
     * 播放地址强制 HTTPS：网易云 CDN 常下发 http 地址，HTTPS 页面与移动端
     * WebView 会按 Mixed Content 拒绝。同地址的 HTTPS 端点可用，改写协议后
     * 所有端拿到的就是 https，无需各自处理。
     *
     * @param array{status: int, body: array, cookies: string[]} $response
     * @return array{status: int, body: array, cookies: string[]}
     */
    private function forceHttpsMedia(array $response): array
    {
        $data = $response['body']['data'] ?? [];
        if (!is_array($data)) {
            return $response;
        }

        foreach ($data as &$item) {
            if (!is_array($item) || !isset($item['url']) || !is_string($item['url'])) {
                continue;
            }
            $item['url'] = $this->upgradeToHttps($item['url']);
        }
        unset($item);
        $response['body']['data'] = $data;

        return $response;
    }

    /**
     * 仅升级网易云媒体域 126.net / 163.com 的 http 地址，第三方域名保持原样。
     */
    private function upgradeToHttps(string $url): string
    {
        if (!str_starts_with($url, 'http://')) {
            return $url;
        }
        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return $url;
        }
        $isNeteaseDomain = $host === '126.net'
            || str_ends_with($host, '.126.net')
            || $host === '163.com'
            || str_ends_with($host, '.163.com');
        if (!$isNeteaseDomain) {
            return $url;
        }

        return 'https://' . substr($url, strlen('http://'));
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
