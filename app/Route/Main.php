<?php

use Anon\Core\Facade\Config;
use Anon\Core\Facade\Route;
use Anon\Controller\Index;
use Anon\Controller\GtaModxProxy;
use Anon\Controller\AiController;
use Anon\Controller\NeteaseProxy;

$defaultCors = Config::get('routing.cors', []);

// 网易云 API
// GET/POST 均接受；前端 external 模式 baseURL 指到 {host}/api 即可
Route::group([
    'prefix' => '/api',
    'cors' => $defaultCors,
], function () {
    // 歌曲播放地址
    Route::any('/song/url', [NeteaseProxy::class, 'songUrl']);
    // 歌曲详情
    Route::any('/song/detail', [NeteaseProxy::class, 'songDetail']);
    // 歌曲红心量/播放量（v1）
    Route::any('/song/detail/v1', [NeteaseProxy::class, 'songDetailV1']);
    // 歌曲评论
    Route::any('/comment/music', [NeteaseProxy::class, 'commentMusic']);
    // 发布/回复/删除评论
    Route::any('/comment', [NeteaseProxy::class, 'comment']);
    // 歌词
    Route::any('/lyric', [NeteaseProxy::class, 'lyric']);
    // 搜索
    Route::any('/cloudsearch', [NeteaseProxy::class, 'search']);
    // 歌单详情
    Route::any('/playlist/detail', [NeteaseProxy::class, 'playlistDetail']);
    // 收藏或取消歌单
    Route::any('/playlist/subscribe', [NeteaseProxy::class, 'playlistSubscribe']);
    // 歌单添加或删除歌曲
    Route::any('/playlist/tracks', [NeteaseProxy::class, 'playlistTracks']);
    // 推荐歌单
    Route::any('/personalized', [NeteaseProxy::class, 'personalized']);
    // 每日推荐歌曲
    Route::any('/recommend/songs', [NeteaseProxy::class, 'recommendSongs']);
    // 二维码 key
    Route::any('/login/qr/key', [NeteaseProxy::class, 'loginQrKey']);
    // 生成二维码内容
    Route::any('/login/qr/create', [NeteaseProxy::class, 'loginQrCreate']);
    // 轮询扫码状态
    Route::any('/login/qr/check', [NeteaseProxy::class, 'loginQrCheck']);
    // 发送手机验证码
    Route::any('/captcha/sent', [NeteaseProxy::class, 'captchaSent']);
    // 手机号登录
    Route::any('/login/cellphone', [NeteaseProxy::class, 'loginCellphone']);
    // 当前登录账号
    Route::any('/user/account', [NeteaseProxy::class, 'userAccount']);
    // 用户歌单
    Route::any('/user/playlist', [NeteaseProxy::class, 'userPlaylist']);
    // 喜欢歌曲列表
    Route::any('/likelist', [NeteaseProxy::class, 'likelist']);
    // 红心或取消红心
    Route::any('/like', [NeteaseProxy::class, 'like']);
    // 歌手信息
    Route::any('/artists', [NeteaseProxy::class, 'artists']);
    // 歌手专辑
    Route::any('/artist/album', [NeteaseProxy::class, 'artistAlbum']);
    // 歌手 MV
    Route::any('/artist/mv', [NeteaseProxy::class, 'artistMv']);
    // 歌手介绍
    Route::any('/artist/desc', [NeteaseProxy::class, 'artistDesc']);
    // 相似歌手
    Route::any('/simi/artist', [NeteaseProxy::class, 'simiArtist']);
    // 专辑详情
    Route::any('/album', [NeteaseProxy::class, 'album']);
    // 收藏的专辑
    Route::any('/album/sublist', [NeteaseProxy::class, 'albumSublist']);
    // 收藏或取消专辑
    Route::any('/album/sub', [NeteaseProxy::class, 'albumSub']);
    // 热门电台
    Route::any('/dj/hot', [NeteaseProxy::class, 'djHot']);
    // 推荐电台
    Route::any('/dj/recommend', [NeteaseProxy::class, 'djRecommend']);
    // 电台详情
    Route::any('/dj/detail', [NeteaseProxy::class, 'djDetail']);
    // 电台节目列表
    Route::any('/dj/program', [NeteaseProxy::class, 'djProgram']);
    // 节目详情
    Route::any('/dj/program/detail', [NeteaseProxy::class, 'djProgramDetail']);
    // 订阅的电台
    Route::any('/dj/sublist', [NeteaseProxy::class, 'djSublist']);
    // 订阅或取消电台
    Route::any('/dj/sub', [NeteaseProxy::class, 'djSub']);
    // MV 播放地址
    Route::any('/mv/url', [NeteaseProxy::class, 'mvUrl']);
    // MV 详情
    Route::any('/mv/detail', [NeteaseProxy::class, 'mvDetail']);
    // 歌曲是否喜欢
    Route::any('/song/like/check', [NeteaseProxy::class, 'songLikeCheck']);
    // 新版歌词
    Route::any('/lyric/new', [NeteaseProxy::class, 'lyricNew']);
    // 首页轮播图
    Route::any('/banner', [NeteaseProxy::class, 'banner']);
    // 推荐新歌
    Route::any('/personalized/newsong', [NeteaseProxy::class, 'personalizedNewsong']);
    // 推荐电台节目
    Route::any('/personalized/djprogram', [NeteaseProxy::class, 'personalizedDjprogram']);
    // 推荐 MV
    Route::any('/personalized/mv', [NeteaseProxy::class, 'personalizedMv']);
    // 所有榜单介绍
    Route::any('/toplist', [NeteaseProxy::class, 'toplist']);
    // 所有榜单内容摘要
    Route::any('/toplist/detail', [NeteaseProxy::class, 'toplistDetail']);
    // 新歌速递
    Route::any('/top/song', [NeteaseProxy::class, 'topSong']);
    // 新碟上架
    Route::any('/top/album', [NeteaseProxy::class, 'topAlbum']);
    // 热门歌手
    Route::any('/top/artists', [NeteaseProxy::class, 'topArtists']);
    // MV 排行
    Route::any('/top/mv', [NeteaseProxy::class, 'topMv']);
    // 分类歌单
    Route::any('/top/playlist', [NeteaseProxy::class, 'topPlaylist']);
    // 热门搜索
    Route::any('/search/hot', [NeteaseProxy::class, 'searchHot']);
    // 搜索建议
    Route::any('/search/suggest', [NeteaseProxy::class, 'searchSuggest']);
    // 相似歌曲
    Route::any('/simi/song', [NeteaseProxy::class, 'simiSong']);
    // 相似歌单
    Route::any('/simi/playlist', [NeteaseProxy::class, 'simiPlaylist']);
    // 相似 MV
    Route::any('/simi/mv', [NeteaseProxy::class, 'simiMv']);
    // 相关视频
    Route::any('/related/allvideo', [NeteaseProxy::class, 'relatedAllvideo']);
    // 歌手歌曲
    Route::any('/artist/songs', [NeteaseProxy::class, 'artistSongs']);
    // 歌手热门歌曲
    Route::any('/artist/top/song', [NeteaseProxy::class, 'artistTopSong']);
    // 关注歌手列表
    Route::any('/artist/sublist', [NeteaseProxy::class, 'artistSublist']);
    // 收藏或取消歌手
    Route::any('/artist/sub', [NeteaseProxy::class, 'artistSub']);
    // 全部 MV
    Route::any('/mv/all', [NeteaseProxy::class, 'mvAll']);
    // 最新 MV
    Route::any('/mv/first', [NeteaseProxy::class, 'mvFirst']);
    // 收藏的 MV
    Route::any('/mv/sublist', [NeteaseProxy::class, 'mvSublist']);
    // 收藏或取消 MV
    Route::any('/mv/sub', [NeteaseProxy::class, 'mvSub']);
    // 私人 FM
    Route::any('/personal_fm', [NeteaseProxy::class, 'personalFm']);
    // 创建歌单
    Route::any('/playlist/create', [NeteaseProxy::class, 'playlistCreate']);
    // 删除歌单
    Route::any('/playlist/delete', [NeteaseProxy::class, 'playlistDelete']);
    // 歌单改名
    Route::any('/playlist/update/name', [NeteaseProxy::class, 'playlistUpdateName']);
    // 歌单改介绍
    Route::any('/playlist/desc/update', [NeteaseProxy::class, 'playlistDescUpdate']);
    // 登录状态
    Route::any('/login/status', [NeteaseProxy::class, 'loginStatus']);
    // 退出登录
    Route::any('/logout', [NeteaseProxy::class, 'logout']);
    // 用户详情
    Route::any('/user/detail', [NeteaseProxy::class, 'userDetail']);
    // 收藏计数
    Route::any('/user/subcount', [NeteaseProxy::class, 'userSubcount']);
    // 用户等级
    Route::any('/user/level', [NeteaseProxy::class, 'userLevel']);
    // 听歌排行
    Route::any('/user/record', [NeteaseProxy::class, 'userRecord']);
    // 新碟上架
    Route::any('/album/new', [NeteaseProxy::class, 'albumNew']);
    // 私人 FM 垃圾桶
    Route::any('/fm_trash', [NeteaseProxy::class, 'fmTrash']);
    // 心动模式 / 智能播放
    Route::any('/playmode/intelligence/list', [NeteaseProxy::class, 'playmodeIntelligenceList']);
    // 云盘列表
    Route::any('/user/cloud', [NeteaseProxy::class, 'userCloud']);
    // 删除云盘歌曲
    Route::any('/user/cloud/del', [NeteaseProxy::class, 'userCloudDel']);
    // 精品歌单标签
    Route::any('/playlist/highquality/tags', [NeteaseProxy::class, 'playlistHighqualityTags']);
    // 每日签到
    Route::any('/daily_signin', [NeteaseProxy::class, 'dailySignin']);
    // 邮箱登录
    Route::any('/login', [NeteaseProxy::class, 'login']);
});

Route::get('/', [Index::class, 'index'])
    ->cors($defaultCors);
Route::get('/docs', [Index::class, 'docs'])
    ->cors($defaultCors);
Route::get('/openapi.json', [Index::class, 'openapiJson'])
    ->cors($defaultCors);
