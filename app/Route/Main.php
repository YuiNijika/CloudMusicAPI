<?php

use Anon\Core\Facade\Config;
use Anon\Core\Facade\Route;
use Anon\Controller\Index;
use Anon\Controller\GtaModxProxy;
use Anon\Controller\AiController;
use Anon\Controller\NeteaseProxy;

$defaultCors = Config::get('routing.cors', []);

Route::get('/', [Index::class, 'index'])
    ->cors($defaultCors);
Route::get('/docs', [Index::class, 'docs'])
    ->cors($defaultCors);
Route::get('/openapi.json', [Index::class, 'openapiJson'])
    ->cors($defaultCors);

// 网易云 API
// GET/POST 均接受；前端 external 模式 baseURL 指到 {host}/api 即可
Route::group([
    'prefix' => '/api',
    'cors' => $defaultCors,
], function () {
    // 歌曲相关
    Route::group(['prefix' => '/song'], function () {
        Route::any('/url', [NeteaseProxy::class, 'songUrl']);
        Route::any('/detail', [NeteaseProxy::class, 'songDetail']);
        Route::any('/detail/v1', [NeteaseProxy::class, 'songDetailV1']);
        Route::any('/like/check', [NeteaseProxy::class, 'songLikeCheck']);
    });

    // 评论相关
    Route::group(['prefix' => '/comment'], function () {
        Route::any('/music', [NeteaseProxy::class, 'commentMusic']);
        Route::any('', [NeteaseProxy::class, 'comment']);
    });

    // 歌词相关
    Route::group(['prefix' => '/lyric'], function () {
        Route::any('', [NeteaseProxy::class, 'lyric']);
        Route::any('/new', [NeteaseProxy::class, 'lyricNew']);
    });

    // 搜索相关
    Route::group(['prefix' => '/search'], function () {
        Route::any('/hot', [NeteaseProxy::class, 'searchHot']);
        Route::any('/suggest', [NeteaseProxy::class, 'searchSuggest']);
    });
    Route::any('/cloudsearch', [NeteaseProxy::class, 'search']);

    // 歌单相关
    Route::group(['prefix' => '/playlist'], function () {
        Route::any('/detail', [NeteaseProxy::class, 'playlistDetail']);
        Route::any('/subscribe', [NeteaseProxy::class, 'playlistSubscribe']);
        Route::any('/tracks', [NeteaseProxy::class, 'playlistTracks']);
        Route::any('/create', [NeteaseProxy::class, 'playlistCreate']);
        Route::any('/delete', [NeteaseProxy::class, 'playlistDelete']);
        Route::any('/update/name', [NeteaseProxy::class, 'playlistUpdateName']);
        Route::any('/desc/update', [NeteaseProxy::class, 'playlistDescUpdate']);
        Route::any('/highquality/tags', [NeteaseProxy::class, 'playlistHighqualityTags']);
    });

    // 登录相关
    Route::group(['prefix' => '/login'], function () {
        Route::group(['prefix' => '/qr'], function () {
            Route::any('/key', [NeteaseProxy::class, 'loginQrKey']);
            Route::any('/create', [NeteaseProxy::class, 'loginQrCreate']);
            Route::any('/check', [NeteaseProxy::class, 'loginQrCheck']);
        });
        Route::any('/cellphone', [NeteaseProxy::class, 'loginCellphone']);
        Route::any('/status', [NeteaseProxy::class, 'loginStatus']);
    });
    Route::any('/login', [NeteaseProxy::class, 'login']);
    Route::any('/captcha/sent', [NeteaseProxy::class, 'captchaSent']);
    Route::any('/logout', [NeteaseProxy::class, 'logout']);

    // 用户相关
    Route::group(['prefix' => '/user'], function () {
        Route::any('/account', [NeteaseProxy::class, 'userAccount']);
        Route::any('/playlist', [NeteaseProxy::class, 'userPlaylist']);
        Route::any('/detail', [NeteaseProxy::class, 'userDetail']);
        Route::any('/subcount', [NeteaseProxy::class, 'userSubcount']);
        Route::any('/level', [NeteaseProxy::class, 'userLevel']);
        Route::any('/record', [NeteaseProxy::class, 'userRecord']);
        Route::group(['prefix' => '/cloud'], function () {
            Route::any('', [NeteaseProxy::class, 'userCloud']);
            Route::any('/del', [NeteaseProxy::class, 'userCloudDel']);
        });
    });

    // 红心相关
    Route::any('/likelist', [NeteaseProxy::class, 'likelist']);
    Route::any('/like', [NeteaseProxy::class, 'like']);

    // 歌手相关
    Route::group(['prefix' => '/artist'], function () {
        Route::any('/album', [NeteaseProxy::class, 'artistAlbum']);
        Route::any('/mv', [NeteaseProxy::class, 'artistMv']);
        Route::any('/desc', [NeteaseProxy::class, 'artistDesc']);
        Route::any('/songs', [NeteaseProxy::class, 'artistSongs']);
        Route::any('/top/song', [NeteaseProxy::class, 'artistTopSong']);
        Route::any('/sublist', [NeteaseProxy::class, 'artistSublist']);
        Route::any('/sub', [NeteaseProxy::class, 'artistSub']);
    });
    Route::any('/artists', [NeteaseProxy::class, 'artists']);

    // 专辑相关
    Route::group(['prefix' => '/album'], function () {
        Route::any('/sublist', [NeteaseProxy::class, 'albumSublist']);
        Route::any('/sub', [NeteaseProxy::class, 'albumSub']);
        Route::any('/new', [NeteaseProxy::class, 'albumNew']);
    });
    Route::any('/album', [NeteaseProxy::class, 'album']);

    // 电台相关
    Route::group(['prefix' => '/dj'], function () {
        Route::any('/hot', [NeteaseProxy::class, 'djHot']);
        Route::any('/recommend', [NeteaseProxy::class, 'djRecommend']);
        Route::any('/detail', [NeteaseProxy::class, 'djDetail']);
        Route::any('/program', [NeteaseProxy::class, 'djProgram']);
        Route::any('/program/detail', [NeteaseProxy::class, 'djProgramDetail']);
        Route::any('/sublist', [NeteaseProxy::class, 'djSublist']);
        Route::any('/sub', [NeteaseProxy::class, 'djSub']);
    });

    // MV 相关
    Route::group(['prefix' => '/mv'], function () {
        Route::any('/url', [NeteaseProxy::class, 'mvUrl']);
        Route::any('/detail', [NeteaseProxy::class, 'mvDetail']);
        Route::any('/all', [NeteaseProxy::class, 'mvAll']);
        Route::any('/first', [NeteaseProxy::class, 'mvFirst']);
        Route::any('/sublist', [NeteaseProxy::class, 'mvSublist']);
        Route::any('/sub', [NeteaseProxy::class, 'mvSub']);
    });

    // 相似推荐相关
    Route::group(['prefix' => '/simi'], function () {
        Route::any('/artist', [NeteaseProxy::class, 'simiArtist']);
        Route::any('/song', [NeteaseProxy::class, 'simiSong']);
        Route::any('/playlist', [NeteaseProxy::class, 'simiPlaylist']);
        Route::any('/mv', [NeteaseProxy::class, 'simiMv']);
    });
    Route::any('/related/allvideo', [NeteaseProxy::class, 'relatedAllvideo']);

    // 排行榜相关
    Route::group(['prefix' => '/top'], function () {
        Route::any('/song', [NeteaseProxy::class, 'topSong']);
        Route::any('/album', [NeteaseProxy::class, 'topAlbum']);
        Route::any('/artists', [NeteaseProxy::class, 'topArtists']);
        Route::any('/mv', [NeteaseProxy::class, 'topMv']);
        Route::any('/playlist', [NeteaseProxy::class, 'topPlaylist']);
    });
    Route::group(['prefix' => '/toplist'], function () {
        Route::any('', [NeteaseProxy::class, 'toplist']);
        Route::any('/detail', [NeteaseProxy::class, 'toplistDetail']);
    });

    // 推荐相关
    Route::group(['prefix' => '/personalized'], function () {
        Route::any('', [NeteaseProxy::class, 'personalized']);
        Route::any('/newsong', [NeteaseProxy::class, 'personalizedNewsong']);
        Route::any('/djprogram', [NeteaseProxy::class, 'personalizedDjprogram']);
        Route::any('/mv', [NeteaseProxy::class, 'personalizedMv']);
    });
    Route::any('/recommend/songs', [NeteaseProxy::class, 'recommendSongs']);

    // 私人 FM 相关
    Route::any('/personal_fm', [NeteaseProxy::class, 'personalFm']);
    Route::any('/fm_trash', [NeteaseProxy::class, 'fmTrash']);

    // 心动模式
    Route::any('/playmode/intelligence/list', [NeteaseProxy::class, 'playmodeIntelligenceList']);

    // 其他
    Route::any('/banner', [NeteaseProxy::class, 'banner']);
    Route::any('/daily_signin', [NeteaseProxy::class, 'dailySignin']);
});
