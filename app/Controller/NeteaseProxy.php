<?php

declare(strict_types=1);

namespace Anon\Controller;

use Anon\Core\Http\Request;
use Anon\Core\Http\Response;
use Anon\Service\NeteaseApiClient;

/**
 * 网易云 API 代理
 */
class NeteaseProxy
{
    private NeteaseApiClient $client;

    public function __construct()
    {
        $this->client = new NeteaseApiClient();
    }

    public function songUrl(Request $request): Response
    {
        return $this->respond('song_url', $request);
    }

    public function songDetail(Request $request): Response
    {
        return $this->respond('song_detail', $request);
    }

    public function lyric(Request $request): Response
    {
        return $this->respond('lyric', $request);
    }

    public function search(Request $request): Response
    {
        return $this->respond('search', $request);
    }

    public function playlistDetail(Request $request): Response
    {
        return $this->respond('playlist_detail', $request);
    }

    public function playlistSubscribe(Request $request): Response
    {
        return $this->respond('playlist_subscribe', $request);
    }

    public function playlistTracks(Request $request): Response
    {
        return $this->respond('playlist_tracks', $request);
    }

    public function personalized(Request $request): Response
    {
        return $this->respond('personalized', $request);
    }

    public function recommendSongs(Request $request): Response
    {
        return $this->respond('recommend_songs', $request);
    }

    public function loginQrKey(Request $request): Response
    {
        return $this->respond('login_qr_key', $request);
    }

    public function loginQrCreate(Request $request): Response
    {
        return $this->respond('login_qr_create', $request);
    }

    public function loginQrCheck(Request $request): Response
    {
        return $this->respond('login_qr_check', $request);
    }

    public function captchaSent(Request $request): Response
    {
        return $this->respond('captcha_sent', $request);
    }

    public function loginCellphone(Request $request): Response
    {
        return $this->respond('login_cellphone', $request);
    }

    public function userAccount(Request $request): Response
    {
        return $this->respond('user_account', $request);
    }

    public function userPlaylist(Request $request): Response
    {
        return $this->respond('user_playlist', $request);
    }

    public function likelist(Request $request): Response
    {
        return $this->respond('likelist', $request);
    }

    public function like(Request $request): Response
    {
        return $this->respond('like', $request);
    }

    public function artists(Request $request): Response
    {
        return $this->respond('artists', $request);
    }

    public function artistAlbum(Request $request): Response
    {
        return $this->respond('artist_album', $request);
    }

    public function artistMv(Request $request): Response
    {
        return $this->respond('artist_mv', $request);
    }

    public function artistDesc(Request $request): Response
    {
        return $this->respond('artist_desc', $request);
    }

    public function simiArtist(Request $request): Response
    {
        return $this->respond('simi_artist', $request);
    }

    public function album(Request $request): Response
    {
        return $this->respond('album', $request);
    }

    public function albumSublist(Request $request): Response
    {
        return $this->respond('album_sublist', $request);
    }

    public function albumSub(Request $request): Response
    {
        return $this->respond('album_sub', $request);
    }

    public function djHot(Request $request): Response
    {
        return $this->respond('dj_hot', $request);
    }

    public function djRecommend(Request $request): Response
    {
        return $this->respond('dj_recommend', $request);
    }

    public function djDetail(Request $request): Response
    {
        return $this->respond('dj_detail', $request);
    }

    public function djProgram(Request $request): Response
    {
        return $this->respond('dj_program', $request);
    }

    public function djProgramDetail(Request $request): Response
    {
        return $this->respond('dj_program_detail', $request);
    }

    public function djSublist(Request $request): Response
    {
        return $this->respond('dj_sublist', $request);
    }

    public function djSub(Request $request): Response
    {
        return $this->respond('dj_sub', $request);
    }

    public function mvUrl(Request $request): Response
    {
        return $this->respond('mv_url', $request);
    }

    public function mvDetail(Request $request): Response
    {
        return $this->respond('mv_detail', $request);
    }

    private function respond(string $name, Request $request): Response
    {
        try {
            $result = $this->client->proxy($name, $request->input());
        } catch (\Throwable $e) {
            return Response::error('网易云请求失败', 502, null, 'NCM_UPSTREAM_ERROR');
        }

        // 统一 envelope：code = 网易云业务码，data = 网易云 data（无 data 字段的接口
        // 把业务字段装进 data，如 { code, unikey } → data: { unikey }），
        // 登录接口额外顶层 cookie。前端解包时 data 保留 + 对象字段展平，兼容各模块读取形状。
        $body = $result['body'];
        $code = is_int($body['code'] ?? null) ? $body['code'] : ($result['status'] ?: 200);
        $data = $body['data'] ?? null;
        if ($data === null) {
            $data = array_filter(
                $body,
                static fn (string $key): bool => !in_array($key, ['code', 'msg', 'message', 'cookie'], true),
                ARRAY_FILTER_USE_KEY,
            );
        }

        // 二维码轮询的 800-803 都是状态而非错误（过期/待扫/已扫/成功，
        // 前端按 code 轮询并写 cookie），其余非 200 视为业务失败：
        // success=false 让前端统一抛错并展示 message
        $isQrPoll = $name === 'login_qr_check';
        $success = $code === 200 || ($isQrPoll && in_array($code, [800, 801, 802, 803], true));

        $envelope = [
            'success' => $success,
            'code' => $code,
            'message' => (string) ($body['msg'] ?? $body['message'] ?? 'OK'),
            'data' => $data,
        ];
        if (($body['cookie'] ?? '') !== '') {
            $envelope['cookie'] = $body['cookie'];
        }

        return Response::json($envelope, 200);
    }
}
