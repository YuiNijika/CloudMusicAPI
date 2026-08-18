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

    /** 歌曲播放地址 */
    public function songUrl(Request $request): Response
    {
        return $this->respond('song_url', $request);
    }

    /** 歌曲详情 */
    public function songDetail(Request $request): Response
    {
        return $this->respond('song_detail', $request);
    }

    /** 歌曲红心量/播放量（v1） */
    public function songDetailV1(Request $request): Response
    {
        return $this->respond('song_detail_v1', $request);
    }

    /** 歌曲评论 */
    public function commentMusic(Request $request): Response
    {
        return $this->respond('comment_music', $request);
    }

    /** 歌词 */
    public function lyric(Request $request): Response
    {
        return $this->respond('lyric', $request);
    }

    /** 搜索 */
    public function search(Request $request): Response
    {
        return $this->respond('search', $request);
    }

    /** 歌单详情 */
    public function playlistDetail(Request $request): Response
    {
        return $this->respond('playlist_detail', $request);
    }

    /** 收藏或取消歌单 */
    public function playlistSubscribe(Request $request): Response
    {
        return $this->respond('playlist_subscribe', $request);
    }

    /** 歌单添加或删除歌曲 */
    public function playlistTracks(Request $request): Response
    {
        return $this->respond('playlist_tracks', $request);
    }

    /** 推荐歌单 */
    public function personalized(Request $request): Response
    {
        return $this->respond('personalized', $request);
    }

    /** 每日推荐歌曲 */
    public function recommendSongs(Request $request): Response
    {
        return $this->respond('recommend_songs', $request);
    }

    /** 二维码 key */
    public function loginQrKey(Request $request): Response
    {
        return $this->respond('login_qr_key', $request);
    }

    /** 生成二维码内容 */
    public function loginQrCreate(Request $request): Response
    {
        return $this->respond('login_qr_create', $request);
    }

    /** 轮询扫码状态 */
    public function loginQrCheck(Request $request): Response
    {
        return $this->respond('login_qr_check', $request);
    }

    /** 发送手机验证码 */
    public function captchaSent(Request $request): Response
    {
        return $this->respond('captcha_sent', $request);
    }

    /** 手机号登录 */
    public function loginCellphone(Request $request): Response
    {
        return $this->respond('login_cellphone', $request);
    }

    /** 当前登录账号 */
    public function userAccount(Request $request): Response
    {
        return $this->respond('user_account', $request);
    }

    /** 用户歌单 */
    public function userPlaylist(Request $request): Response
    {
        return $this->respond('user_playlist', $request);
    }

    /** 喜欢歌曲列表 */
    public function likelist(Request $request): Response
    {
        return $this->respond('likelist', $request);
    }

    /** 红心或取消红心 */
    public function like(Request $request): Response
    {
        return $this->respond('like', $request);
    }

    /** 歌手信息 */
    public function artists(Request $request): Response
    {
        return $this->respond('artists', $request);
    }

    /** 歌手专辑 */
    public function artistAlbum(Request $request): Response
    {
        return $this->respond('artist_album', $request);
    }

    /** 歌手 MV */
    public function artistMv(Request $request): Response
    {
        return $this->respond('artist_mv', $request);
    }

    /** 歌手介绍 */
    public function artistDesc(Request $request): Response
    {
        return $this->respond('artist_desc', $request);
    }

    /** 相似歌手 */
    public function simiArtist(Request $request): Response
    {
        return $this->respond('simi_artist', $request);
    }

    /** 专辑详情 */
    public function album(Request $request): Response
    {
        return $this->respond('album', $request);
    }

    /** 收藏的专辑 */
    public function albumSublist(Request $request): Response
    {
        return $this->respond('album_sublist', $request);
    }

    /** 收藏或取消专辑 */
    public function albumSub(Request $request): Response
    {
        return $this->respond('album_sub', $request);
    }

    /** 热门电台 */
    public function djHot(Request $request): Response
    {
        return $this->respond('dj_hot', $request);
    }

    /** 推荐电台 */
    public function djRecommend(Request $request): Response
    {
        return $this->respond('dj_recommend', $request);
    }

    /** 电台详情 */
    public function djDetail(Request $request): Response
    {
        return $this->respond('dj_detail', $request);
    }

    /** 电台节目列表 */
    public function djProgram(Request $request): Response
    {
        return $this->respond('dj_program', $request);
    }

    /** 节目详情 */
    public function djProgramDetail(Request $request): Response
    {
        return $this->respond('dj_program_detail', $request);
    }

    /** 订阅的电台 */
    public function djSublist(Request $request): Response
    {
        return $this->respond('dj_sublist', $request);
    }

    /** 订阅或取消电台 */
    public function djSub(Request $request): Response
    {
        return $this->respond('dj_sub', $request);
    }

    /** MV 播放地址 */
    public function mvUrl(Request $request): Response
    {
        return $this->respond('mv_url', $request);
    }

    /** MV 详情 */
    public function mvDetail(Request $request): Response
    {
        return $this->respond('mv_detail', $request);
    }

    /** 歌曲是否喜欢 */
    public function songLikeCheck(Request $request): Response
    {
        return $this->respond('song_like_check', $request);
    }

    /** 新版歌词 */
    public function lyricNew(Request $request): Response
    {
        return $this->respond('lyric_new', $request);
    }

    /** 首页轮播图 */
    public function banner(Request $request): Response
    {
        return $this->respond('banner', $request);
    }

    /** 推荐新歌 */
    public function personalizedNewsong(Request $request): Response
    {
        return $this->respond('personalized_newsong', $request);
    }

    /** 推荐电台节目 */
    public function personalizedDjprogram(Request $request): Response
    {
        return $this->respond('personalized_djprogram', $request);
    }

    /** 推荐 MV */
    public function personalizedMv(Request $request): Response
    {
        return $this->respond('personalized_mv', $request);
    }

    /** 所有榜单介绍 */
    public function toplist(Request $request): Response
    {
        return $this->respond('toplist', $request);
    }

    /** 所有榜单内容摘要 */
    public function toplistDetail(Request $request): Response
    {
        return $this->respond('toplist_detail', $request);
    }

    /** 新歌速递 */
    public function topSong(Request $request): Response
    {
        return $this->respond('top_song', $request);
    }

    /** 新碟上架 */
    public function topAlbum(Request $request): Response
    {
        return $this->respond('top_album', $request);
    }

    /** 热门歌手 */
    public function topArtists(Request $request): Response
    {
        return $this->respond('top_artists', $request);
    }

    /** MV 排行 */
    public function topMv(Request $request): Response
    {
        return $this->respond('top_mv', $request);
    }

    /** 分类歌单 */
    public function topPlaylist(Request $request): Response
    {
        return $this->respond('top_playlist', $request);
    }

    /** 热门搜索 */
    public function searchHot(Request $request): Response
    {
        return $this->respond('search_hot', $request);
    }

    /** 搜索建议 */
    public function searchSuggest(Request $request): Response
    {
        return $this->respond('search_suggest', $request);
    }

    /** 相似歌曲 */
    public function simiSong(Request $request): Response
    {
        return $this->respond('simi_song', $request);
    }

    /** 相似歌单 */
    public function simiPlaylist(Request $request): Response
    {
        return $this->respond('simi_playlist', $request);
    }

    /** 相似 MV */
    public function simiMv(Request $request): Response
    {
        return $this->respond('simi_mv', $request);
    }

    /** 相关视频 */
    public function relatedAllvideo(Request $request): Response
    {
        return $this->respond('related_allvideo', $request);
    }

    /** 歌手歌曲 */
    public function artistSongs(Request $request): Response
    {
        return $this->respond('artist_songs', $request);
    }

    /** 歌手热门歌曲 */
    public function artistTopSong(Request $request): Response
    {
        return $this->respond('artist_top_song', $request);
    }

    /** 关注歌手列表 */
    public function artistSublist(Request $request): Response
    {
        return $this->respond('artist_sublist', $request);
    }

    /** 收藏或取消歌手 */
    public function artistSub(Request $request): Response
    {
        return $this->respond('artist_sub', $request);
    }

    /** 全部 MV */
    public function mvAll(Request $request): Response
    {
        return $this->respond('mv_all', $request);
    }

    /** 最新 MV */
    public function mvFirst(Request $request): Response
    {
        return $this->respond('mv_first', $request);
    }

    /** 收藏的 MV */
    public function mvSublist(Request $request): Response
    {
        return $this->respond('mv_sublist', $request);
    }

    /** 收藏或取消 MV */
    public function mvSub(Request $request): Response
    {
        return $this->respond('mv_sub', $request);
    }

    /** 私人 FM */
    public function personalFm(Request $request): Response
    {
        return $this->respond('personal_fm', $request);
    }

    /** 创建歌单 */
    public function playlistCreate(Request $request): Response
    {
        return $this->respond('playlist_create', $request);
    }

    /** 删除歌单 */
    public function playlistDelete(Request $request): Response
    {
        return $this->respond('playlist_delete', $request);
    }

    /** 歌单改名 */
    public function playlistUpdateName(Request $request): Response
    {
        return $this->respond('playlist_update_name', $request);
    }

    /** 歌单改介绍 */
    public function playlistDescUpdate(Request $request): Response
    {
        return $this->respond('playlist_desc_update', $request);
    }

    /** 登录状态 */
    public function loginStatus(Request $request): Response
    {
        return $this->respond('login_status', $request);
    }

    /** 退出登录 */
    public function logout(Request $request): Response
    {
        return $this->respond('logout', $request);
    }

    /** 用户详情 */
    public function userDetail(Request $request): Response
    {
        return $this->respond('user_detail', $request);
    }

    /** 收藏计数 */
    public function userSubcount(Request $request): Response
    {
        return $this->respond('user_subcount', $request);
    }

    /** 用户等级 */
    public function userLevel(Request $request): Response
    {
        return $this->respond('user_level', $request);
    }

    /** 听歌排行 */
    public function userRecord(Request $request): Response
    {
        return $this->respond('user_record', $request);
    }

    /** 新碟上架 */
    public function albumNew(Request $request): Response
    {
        return $this->respond('album_new', $request);
    }

    /** 私人 FM 垃圾桶 */
    public function fmTrash(Request $request): Response
    {
        return $this->respond('fm_trash', $request);
    }

    /** 心动模式 / 智能播放 */
    public function playmodeIntelligenceList(Request $request): Response
    {
        return $this->respond('playmode_intelligence_list', $request);
    }

    /** 云盘列表 */
    public function userCloud(Request $request): Response
    {
        return $this->respond('user_cloud', $request);
    }

    /** 删除云盘歌曲 */
    public function userCloudDel(Request $request): Response
    {
        return $this->respond('user_cloud_del', $request);
    }

    /** 精品歌单标签 */
    public function playlistHighqualityTags(Request $request): Response
    {
        return $this->respond('playlist_highquality_tags', $request);
    }

    /** 每日签到 */
    public function dailySignin(Request $request): Response
    {
        return $this->respond('daily_signin', $request);
    }

    /** 邮箱登录 */
    public function login(Request $request): Response
    {
        return $this->respond('login', $request);
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
