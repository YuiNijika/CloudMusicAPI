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
    Route::any('/song/url', [NeteaseProxy::class, 'songUrl']);
    Route::any('/song/detail', [NeteaseProxy::class, 'songDetail']);
    Route::any('/lyric', [NeteaseProxy::class, 'lyric']);
    Route::any('/cloudsearch', [NeteaseProxy::class, 'search']);
    Route::any('/playlist/detail', [NeteaseProxy::class, 'playlistDetail']);
    Route::any('/playlist/subscribe', [NeteaseProxy::class, 'playlistSubscribe']);
    Route::any('/playlist/tracks', [NeteaseProxy::class, 'playlistTracks']);
    Route::any('/personalized', [NeteaseProxy::class, 'personalized']);
    Route::any('/recommend/songs', [NeteaseProxy::class, 'recommendSongs']);
    Route::any('/login/qr/key', [NeteaseProxy::class, 'loginQrKey']);
    Route::any('/login/qr/create', [NeteaseProxy::class, 'loginQrCreate']);
    Route::any('/login/qr/check', [NeteaseProxy::class, 'loginQrCheck']);
    Route::any('/captcha/sent', [NeteaseProxy::class, 'captchaSent']);
    Route::any('/login/cellphone', [NeteaseProxy::class, 'loginCellphone']);
    Route::any('/user/account', [NeteaseProxy::class, 'userAccount']);
    Route::any('/user/playlist', [NeteaseProxy::class, 'userPlaylist']);
    Route::any('/likelist', [NeteaseProxy::class, 'likelist']);
    Route::any('/like', [NeteaseProxy::class, 'like']);
    Route::any('/artists', [NeteaseProxy::class, 'artists']);
    Route::any('/artist/album', [NeteaseProxy::class, 'artistAlbum']);
    Route::any('/artist/mv', [NeteaseProxy::class, 'artistMv']);
    Route::any('/artist/desc', [NeteaseProxy::class, 'artistDesc']);
    Route::any('/simi/artist', [NeteaseProxy::class, 'simiArtist']);
    Route::any('/album', [NeteaseProxy::class, 'album']);
    Route::any('/album/sublist', [NeteaseProxy::class, 'albumSublist']);
    Route::any('/album/sub', [NeteaseProxy::class, 'albumSub']);
    Route::any('/dj/hot', [NeteaseProxy::class, 'djHot']);
    Route::any('/dj/recommend', [NeteaseProxy::class, 'djRecommend']);
    Route::any('/dj/detail', [NeteaseProxy::class, 'djDetail']);
    Route::any('/dj/program', [NeteaseProxy::class, 'djProgram']);
    Route::any('/dj/program/detail', [NeteaseProxy::class, 'djProgramDetail']);
    Route::any('/dj/sublist', [NeteaseProxy::class, 'djSublist']);
    Route::any('/dj/sub', [NeteaseProxy::class, 'djSub']);
    Route::any('/mv/url', [NeteaseProxy::class, 'mvUrl']);
    Route::any('/mv/detail', [NeteaseProxy::class, 'mvDetail']);
});

Route::get('/', [Index::class, 'index'])
    ->cors($defaultCors);
Route::get('/docs', [Index::class, 'docs'])
    ->cors($defaultCors);
Route::get('/openapi.json', [Index::class, 'openapiJson'])
    ->cors($defaultCors);
