<?php

namespace Anon\Controller;

use Anon\Core\Http\Request;
use Anon\Core\Http\Response;

/**
 * 网易云音乐 API 服务入口
 *
 * 首页：接口介绍；/docs：SwaggerUI；/openapi.json：OpenAPI 3.0 规范。
 * 全部 /api/* 接口透传网易云并统一 envelope（见 NeteaseProxy）。
 */
class Index
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index(): Response
    {
        return (new Response($this->indexTemplate(), 200))
            ->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    /** SwaggerUI 文档页：spec 指向同源的 /openapi.json */
    public function docs(): Response
    {
        return (new Response($this->docsTemplate(), 200))
            ->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    /** OpenAPI 3.0 规范：81 个接口的路径与参数（供 SwaggerUI / 客户端生成） */
    public function openapiJson(): Response
    {
        return Response::json($this->openapiSpec(), 200);
    }

    private function indexTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>网易云音乐 API</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ink: '#0a0f17',
                        line: 'rgba(128, 162, 255, 0.14)',
                        accent: '#6ee7ff',
                        accent2: '#8b7cff',
                    },
                    boxShadow: {
                        soft: '0 24px 80px rgba(0, 0, 0, 0.28)',
                    },
                    fontFamily: {
                        sans: ['Inter', 'Segoe UI', 'PingFang SC', 'Microsoft YaHei', 'sans-serif'],
                        display: ['Space Grotesk', 'Inter', 'Segoe UI', 'sans-serif'],
                        mono: ['IBM Plex Mono', 'Cascadia Mono', 'Consolas', 'monospace'],
                    },
                },
            },
        };
    </script>
    <style>
        body {
            background:
                radial-gradient(circle at 20% 0%, rgba(110, 231, 255, 0.14), transparent 28%),
                radial-gradient(circle at 80% 0%, rgba(139, 124, 255, 0.14), transparent 24%),
                linear-gradient(180deg, #0b1018 0%, #070b12 100%);
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.025) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: linear-gradient(180deg, black, transparent 80%);
            opacity: 0.4;
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 antialiased">
    <div class="relative mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-4 py-7 sm:flex-row sm:items-center sm:justify-between">
            <div class="inline-flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl border border-white/10 bg-white/5 font-display text-sm font-semibold text-accent shadow-soft">N</span>
                <span class="block">
                    <span class="block font-display text-base font-semibold tracking-tight text-white">网易云音乐 API</span>
                    <span class="font-mono text-[0.72rem] uppercase tracking-[0.18em] text-slate-400">netease cloud music</span>
                </span>
            </div>

            <nav class="flex flex-wrap gap-2 text-[0.72rem] font-mono uppercase tracking-[0.14em] text-slate-400" aria-label="快速入口">
                <a href="/docs" class="rounded-full border border-accent/30 bg-accent/10 px-3 py-2 transition hover:border-accent/50 hover:text-white">/docs SwaggerUI</a>
                <a href="/openapi.json" class="rounded-full border border-white/10 px-3 py-2 transition hover:border-accent/40 hover:text-white">/openapi.json</a>
                <a href="https://github.com/YuiNijika/CloudMusicAPI" target="_blank" rel="noopener" class="rounded-full border border-white/10 px-3 py-2 transition hover:border-accent/40 hover:text-white">GitHub ↗</a>
            </nav>
        </header>

        <main class="pb-20 pt-8 sm:pt-12">
            <div class="mb-4 font-mono text-[0.74rem] uppercase tracking-[0.22em] text-accent">Base URL: {host}/api · 81 endpoints</div>
            <h1 class="max-w-4xl font-display text-5xl font-semibold leading-none tracking-[-0.08em] text-white sm:text-6xl lg:text-7xl">
                网易云音乐 API
            </h1>

            <p class="mt-6 max-w-2xl text-base leading-8 text-slate-400 sm:text-lg">
                播放地址、歌词、搜索、歌单、登录、用户、艺人、专辑、电台、MV ——
                全部接口统一 envelope 格式，登录凭证经 <code class="rounded bg-white/5 px-2 py-1 font-mono text-sm text-slate-200">cookie</code> 参数透传。
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="/docs" class="inline-flex min-h-11 items-center justify-center rounded-full border border-accent/30 bg-gradient-to-r from-accent/15 to-accent2/15 px-5 text-sm font-semibold text-white transition hover:border-accent/50">打开文档</a>
                <a href="/openapi.json" class="inline-flex min-h-11 items-center justify-center rounded-full border border-white/10 bg-white/5 px-5 text-sm font-semibold text-slate-300 transition hover:border-white/20 hover:text-white">OpenAPI 规范</a>
            </div>

            <section class="mt-10 overflow-hidden rounded-3xl border border-white/10 bg-slate-950/70 shadow-soft" aria-label="快速开始">
                <div class="flex items-center justify-between gap-3 border-b border-white/10 px-5 py-4 text-[0.72rem] uppercase tracking-[0.16em] text-slate-500">
                    <span class="font-mono">快速开始</span>
                    <span class="font-mono text-accent">curl</span>
                </div>
                <pre class="overflow-x-auto p-5 font-mono text-sm leading-7 text-slate-200"><span class="text-slate-400"># 搜索歌曲</span>
curl <span class="text-accent2">"{host}/api/cloudsearch?keywords=海阔天空&limit=3"</span>

<span class="text-slate-400"># 获取播放地址（登录态经 cookie 透传）</span>
curl <span class="text-accent2">"{host}/api/song/url?id=33894312&br=320000"</span>

<span class="text-slate-400"># 统一响应结构</span>
<span class="text-emerald-300">{</span> <span class="text-accent">"success"</span>: <span class="text-emerald-300">true</span>, <span class="text-accent">"code"</span>: <span class="text-emerald-300">200</span>,
  <span class="text-accent">"message"</span>: <span class="text-emerald-300">"OK"</span>, <span class="text-accent">"data"</span>: <span class="text-emerald-300">{</span> ... <span class="text-emerald-300">}</span> <span class="text-emerald-300">}</span></pre>
            </section>

            <div class="mt-6 grid gap-4 md:grid-cols-2" aria-label="接口分类">
                <a href="/docs" class="rounded-3xl border border-white/10 bg-white/5 p-5 transition hover:border-accent/30 hover:bg-white/[0.07]">
                    <div class="font-mono text-[0.72rem] uppercase tracking-[0.16em] text-accent">播放 · 搜索 · 歌单</div>
                    <div class="mt-2 text-lg font-semibold text-white">song/url · lyric · cloudsearch · playlist/*</div>
                    <p class="mt-3 text-sm leading-7 text-slate-400">播放地址、歌词、搜索与歌单操作，音质可调。</p>
                </a>
                <a href="/docs" class="rounded-3xl border border-white/10 bg-white/5 p-5 transition hover:border-accent/30 hover:bg-white/[0.07]">
                    <div class="font-mono text-[0.72rem] uppercase tracking-[0.16em] text-accent">登录 · 用户</div>
                    <div class="mt-2 text-lg font-semibold text-white">login/qr/* · login/cellphone · user/*</div>
                    <p class="mt-3 text-sm leading-7 text-slate-400">扫码与手机验证码登录，登录态 cookie 回传。</p>
                </a>
                <a href="/docs" class="rounded-3xl border border-white/10 bg-white/5 p-5 transition hover:border-accent/30 hover:bg-white/[0.07]">
                    <div class="font-mono text-[0.72rem] uppercase tracking-[0.16em] text-accent">艺人 · 专辑</div>
                    <div class="mt-2 text-lg font-semibold text-white">artists · artist/* · album/*</div>
                    <p class="mt-3 text-sm leading-7 text-slate-400">艺人详情、专辑列表、相似艺人。</p>
                </a>
                <a href="/docs" class="rounded-3xl border border-white/10 bg-white/5 p-5 transition hover:border-accent/30 hover:bg-white/[0.07]">
                    <div class="font-mono text-[0.72rem] uppercase tracking-[0.16em] text-accent">电台 · MV</div>
                    <div class="mt-2 text-lg font-semibold text-white">dj/* · mv/url · mv/detail</div>
                    <p class="mt-3 text-sm leading-7 text-slate-400">播客电台节目与 MV 播放地址。</p>
                </a>
            </div>

            <footer class="pt-10">
                <p class="max-w-2xl text-sm leading-7 text-slate-500">
                完整接口清单见 <a href="/docs" class="text-accent underline underline-offset-3 hover:text-white">SwaggerUI 文档</a>，规范文件在 <code class="rounded bg-white/5 px-1.5 py-0.5 font-mono text-xs text-slate-300">/openapi.json</code>。
                </p>
            </footer>
        </main>
    </div>
</body>
</html>
HTML;
    }

    private function docsTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>调用文档 - 网易云音乐 API</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <style>
        body { margin: 0; }
        .swagger-ui .topbar { display: none; }
        @media (prefers-color-scheme: dark) {
            body { background: #0b1018; }
            .swagger-ui { background: #0b1018; color: #dbe4f0; }
            .swagger-ui .info .title,
            .swagger-ui .info .title small,
            .swagger-ui .opblock-tag,
            .swagger-ui .opblock .opblock-summary-description,
            .swagger-ui .opblock .opblock-summary-operation-id,
            .swagger-ui .opblock .opblock-summary-path,
            .swagger-ui .model-title { color: #e6edf7; }
            .swagger-ui .info p,
            .swagger-ui .info li,
            .swagger-ui .info table,
            .swagger-ui .opblock-description-wrapper p,
            .swagger-ui .opblock .parameter__name,
            .swagger-ui .opblock .parameter__type,
            .swagger-ui .opblock .parameter__in,
            .swagger-ui .response-col_description,
            .swagger-ui .response-col_status,
            .swagger-ui .response-col_links,
            .swagger-ui .tab li,
            .swagger-ui table thead tr td,
            .swagger-ui table thead tr th { color: #c6d2e3; }
            .swagger-ui .opblock-tag { border-bottom: 1px solid rgba(255, 255, 255, 0.12); }
            .swagger-ui .opblock { background: transparent; border-color: rgba(255, 255, 255, 0.14); }
            .swagger-ui .opblock .opblock-summary { border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
            .swagger-ui .opblock .opblock-body { background: rgba(255, 255, 255, 0.04); }
            .swagger-ui .opblock .opblock-section-header,
            .swagger-ui .scheme-container,
            .swagger-ui section.models { background: rgba(255, 255, 255, 0.05); box-shadow: none; }
            .swagger-ui .opblock .opblock-section-header h4,
            .swagger-ui section.models h4,
            .swagger-ui .scheme-container .schemes-title { color: #e6edf7; }
            .swagger-ui input[type="text"],
            .swagger-ui select,
            .swagger-ui textarea {
                background: rgba(255, 255, 255, 0.08);
                border-color: rgba(255, 255, 255, 0.2);
                color: #e6edf7;
            }
            .swagger-ui .btn { background: rgba(255, 255, 255, 0.08); color: #e6edf7; }
            .swagger-ui .btn.execute { background: #4f9e55; color: #fff; }
            .swagger-ui .btn.cancel { background: #b0392f; color: #fff; }
            .swagger-ui .dialog-ux .backdrop-ux { background: rgba(0, 0, 0, 0.7); }
            .swagger-ui .dialog-ux .modal-ux {
                background: #121a26;
                border: 1px solid rgba(255, 255, 255, 0.14);
            }
            .swagger-ui .dialog-ux .modal-ux-content h4,
            .swagger-ui .dialog-ux .modal-ux-content p { color: #e6edf7; }
            .swagger-ui .highlight-code,
            .swagger-ui pre.microlight,
            .swagger-ui .model-box,
            .swagger-ui .model {
                background: rgba(255, 255, 255, 0.06);
                color: #c6d2e3;
            }
            .swagger-ui .model-box .model .property,
            .swagger-ui .model-box .model .prop-format { color: #8fb4ff; }
            .swagger-ui .prop-type { color: #b98cff; }
            .swagger-ui .tab li { border-bottom: 2px solid transparent; }
            .swagger-ui .tab li.active { color: #6ee7ff; border-bottom-color: #6ee7ff; }
            .swagger-ui .copy-to-clipboard {
                background: rgba(255, 255, 255, 0.12);
                color: #c6d2e3;
            }
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        window.onload = () => {
            window.ui = SwaggerUIBundle({
                url: '/openapi.json',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [SwaggerUIBundle.presets.apis],
                layout: 'BaseLayout',
            })
        }
    </script>
</body>
</html>
HTML;
    }

    /** 81 个接口的 OpenAPI 3.0 描述；参数与 NeteaseApiClient::buildData 对齐 */
    private function openapiSpec(): array
    {
        $int = static fn (string $name, string $desc, int $default = 0): array => [
            'name' => $name, 'in' => 'query', 'schema' => ['type' => 'integer'], 'default' => $default,
            'description' => $desc,
        ];
        $str = static fn (string $name, string $desc, string $default = ''): array => [
            'name' => $name, 'in' => 'query', 'schema' => ['type' => 'string'], 'default' => $default,
            'description' => $desc,
        ];

        $get = static fn (string $summary, array $parameters = []): array => [
            'get' => array_filter([
                'summary' => $summary,
                'parameters' => array_merge($parameters, [
                    $str('cookie', '登录凭证（MUSIC_U 等，用于登录态接口）'),
                    $str('realIP', '客户端 IP，绕过网易云 IP 风控'),
                ]),
            ]),
        ];

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => '网易云音乐 API',
                'version' => '1.0.0',
                'description' => '网易云音乐接口代理：统一 envelope 响应（success/code/message/data/cookie?），登录接口额外回传 set-cookie。',
            ],
            'servers' => [['url' => '/api', 'description' => '接口前缀']],
            'paths' => [
                '/song/url' => $get('获取歌曲播放地址', [$str('id', '歌曲 id，逗号分隔多个', ''), $int('br', '码率，默认 999000')]),
                '/song/detail' => $get('获取歌曲详情', [$str('ids', '歌曲 id，逗号分隔', '')]),
                '/lyric' => $get('获取歌词', [$str('id', '歌曲 id', '')]),
                '/cloudsearch' => $get('搜索', [$str('keywords', '关键词', ''), $int('type', '1=单曲 10=专辑 100=歌手 1000=歌单', 1), $int('limit', '数量', 30), $int('offset', '偏移', 0)]),
                '/playlist/detail' => $get('获取歌单详情', [$str('id', '歌单 id', ''), $int('s', '最近收藏者数量', 8)]),
                '/playlist/subscribe' => $get('收藏/取消收藏歌单', [$str('id', '歌单 id', ''), $int('t', '1=收藏 0=取消', 0)]),
                '/playlist/tracks' => $get('歌单添加/删除歌曲', [$str('op', 'add/del', 'add'), $str('pid', '歌单 id', ''), $str('tracks', '歌曲 id 逗号分隔', '')]),
                '/personalized' => $get('推荐歌单', [$int('limit', '数量', 30)]),
                '/recommend/songs' => $get('每日推荐歌曲（需登录）', [$str('afresh', 'true 换一批', '')]),
                '/login/qr/key' => $get('二维码 key（登录第一步）'),
                '/login/qr/create' => $get('生成二维码内容', [$str('key', '二维码 key', '')]),
                '/login/qr/check' => $get('轮询扫码状态', [$str('key', '二维码 key', '')]),
                '/captcha/sent' => $get('发送手机验证码', [$str('phone', '手机号', ''), $str('ctcode', '国家码', '86')]),
                '/login/cellphone' => $get('手机号登录', [$str('phone', '手机号', ''), $str('countrycode', '国家码', ''), $str('captcha', '验证码', ''), $str('password', '密码（与验证码二选一）', '')]),
                '/user/account' => $get('当前登录账号信息'),
                '/user/playlist' => $get('用户歌单', [$str('uid', '用户 id', ''), $int('limit', '数量', 30), $int('offset', '偏移', 0)]),
                '/likelist' => $get('喜欢歌曲列表', [$str('uid', '用户 id', '')]),
                '/like' => $get('红心/取消红心', [$str('id', '歌曲 id', ''), $int('like', '1=红心 0=取消', 1)]),
                '/artists' => $get('获取歌手信息', [$str('id', '歌手 id', '')]),
                '/artist/album' => $get('歌手专辑', [$str('id', '歌手 id', ''), $int('limit', '数量', 30), $int('offset', '偏移', 0)]),
                '/artist/mv' => $get('歌手 MV', [$str('id', '歌手 id', ''), $int('limit', '数量', 40), $int('offset', '偏移', 0)]),
                '/artist/desc' => $get('歌手介绍', [$str('id', '歌手 id', '')]),
                '/simi/artist' => $get('相似歌手', [$str('id', '歌手 id', '')]),
                '/album' => $get('专辑详情', [$str('id', '专辑 id', '')]),
                '/album/sublist' => $get('收藏的专辑', [$int('limit', '数量', 25), $int('offset', '偏移', 0)]),
                '/album/sub' => $get('收藏/取消收藏专辑', [$str('id', '专辑 id', ''), $int('t', '1=收藏 0=取消', 0)]),
                '/dj/hot' => $get('热门电台', [$int('limit', '数量', 30), $int('offset', '偏移', 0)]),
                '/dj/recommend' => $get('推荐电台'),
                '/dj/detail' => $get('电台详情', [$str('rid', '电台 id', '')]),
                '/dj/program' => $get('电台节目列表', [$str('rid', '电台 id', ''), $int('limit', '数量', 30), $int('offset', '偏移', 0), $int('asc', '排序', 0)]),
                '/dj/program/detail' => $get('节目详情', [$str('id', '节目 id', '')]),
                '/dj/sublist' => $get('订阅的电台', [$int('limit', '数量', 30), $int('offset', '偏移', 0)]),
                '/dj/sub' => $get('订阅/取消订阅电台', [$str('rid', '电台 id', ''), $int('t', '1=订阅 0=取消', 0)]),
                '/mv/url' => $get('MV 播放地址', [$str('id', 'MV id', ''), $int('r', '清晰度', 1080)]),
                '/mv/detail' => $get('MV 详情', [$str('id', 'MV id', '')]),
                '/song/like/check' => $get('歌曲是否喜欢', [$str('ids', '歌曲 id 逗号分隔', '')]),
                '/lyric/new' => $get('新版歌词（含逐字）', [$str('id', '歌曲 id', '')]),
                '/banner' => $get('首页轮播图', [$int('type', '0=pc 1=android 2=iphone 3=ipad', 0)]),
                '/personalized/newsong' => $get('推荐新歌', [$int('limit', '数量', 10), $int('areaId', '地区 id', 0)]),
                '/personalized/djprogram' => $get('推荐电台节目'),
                '/personalized/mv' => $get('推荐 MV'),
                '/toplist' => $get('所有榜单介绍'),
                '/toplist/detail' => $get('所有榜单内容摘要'),
                '/top/song' => $get('新歌速递', [$int('type', '0=全部 7=华语 96=欧美 8=日本 16=韩国', 0)]),
                '/top/album' => $get('新碟上架', [$str('area', 'ALL/ZH/EA/KR/JP', 'ALL'), $int('limit', '数量', 50), $int('offset', '偏移', 0), $str('type', 'new/hot', 'new'), $int('year', '年份', 0), $int('month', '月份', 0)]),
                '/top/artists' => $get('热门歌手', [$int('limit', '数量', 50), $int('offset', '偏移', 0)]),
                '/top/mv' => $get('MV 排行', [$str('area', '地区', ''), $int('limit', '数量', 30), $int('offset', '偏移', 0)]),
                '/top/playlist' => $get('分类歌单', [$str('cat', '分类，默认全部', '全部'), $str('order', 'hot/new', 'hot'), $int('limit', '数量', 50), $int('offset', '偏移', 0)]),
                '/search/hot' => $get('热门搜索'),
                '/search/suggest' => $get('搜索建议', [$str('keywords', '关键词', '')]),
                '/simi/song' => $get('相似歌曲', [$str('id', '歌曲 id', ''), $int('limit', '数量', 50), $int('offset', '偏移', 0)]),
                '/simi/playlist' => $get('相似歌单', [$str('id', '歌曲 id', ''), $int('limit', '数量', 50), $int('offset', '偏移', 0)]),
                '/simi/mv' => $get('相似 MV', [$str('mvid', 'MV id', '')]),
                '/related/allvideo' => $get('相关视频', [$str('id', '视频 id', '')]),
                '/artist/songs' => $get('歌手歌曲', [$str('id', '歌手 id', ''), $str('order', 'hot/time', 'hot'), $int('limit', '数量', 100), $int('offset', '偏移', 0)]),
                '/artist/top/song' => $get('歌手热门 50 首', [$str('id', '歌手 id', '')]),
                '/artist/sublist' => $get('关注歌手列表', [$int('limit', '数量', 25), $int('offset', '偏移', 0)]),
                '/artist/sub' => $get('收藏/取消收藏歌手', [$str('id', '歌手 id', ''), $int('t', '1=收藏 0=取消', 0)]),
                '/mv/all' => $get('全部 MV', [$str('area', '地区', '全部'), $str('type', '类型', '全部'), $str('order', '排序', '上升最快'), $int('limit', '数量', 30), $int('offset', '偏移', 0)]),
                '/mv/first' => $get('最新 MV', [$str('area', '地区', ''), $int('limit', '数量', 30)]),
                '/mv/sublist' => $get('收藏的 MV', [$int('limit', '数量', 25), $int('offset', '偏移', 0)]),
                '/mv/sub' => $get('收藏/取消收藏 MV', [$str('mvid', 'MV id', ''), $int('t', '1=收藏 0=取消', 0)]),
                '/personal_fm' => $get('私人 FM'),
                '/playlist/create' => $get('创建歌单', [$str('name', '歌单名', ''), $str('privacy', '0=公开 10=隐私', '0'), $str('type', 'NORMAL/VIDEO/SHARED', 'NORMAL')]),
                '/playlist/delete' => $get('删除歌单', [$str('id', '歌单 id', '')]),
                '/login/status' => $get('登录状态'),
                '/logout' => $get('退出登录'),
                '/user/detail' => $get('用户详情', [$str('uid', '用户 id', '')]),
                '/user/subcount' => $get('收藏计数'),
                '/user/level' => $get('用户等级'),
                '/user/record' => $get('听歌排行', [$str('uid', '用户 id', ''), $int('type', '0=所有时间 1=最近一周', 0)]),
                '/playlist/update/name' => $get('歌单改名', [$str('id', '歌单 id', ''), $str('name', '新歌单名', '')]),
                '/playlist/desc/update' => $get('歌单改介绍', [$str('id', '歌单 id', ''), $str('desc', '歌单介绍', '')]),
                '/playlist/highquality/tags' => $get('精品歌单标签'),
                '/album/new' => $get('新碟上架', [$str('area', '地区 ALL/ZH/EA/KR/JP', 'ALL'), $int('limit', '数量', 30), $int('offset', '偏移', 0)]),
                '/fm_trash' => $get('私人 FM 垃圾桶', [$str('id', '歌曲 id', '')]),
                '/playmode/intelligence/list' => $get('心动模式 / 智能播放', [$str('id', '起始歌曲 id', ''), $str('pid', '歌单 id', ''), $int('count', '数量', 1)]),
                '/user/cloud' => $get('云盘歌曲列表', [$int('limit', '数量', 30), $int('offset', '偏移', 0)]),
                '/user/cloud/del' => $get('删除云盘歌曲', [$str('id', '歌曲 id', '')]),
                '/daily_signin' => $get('每日签到', [$int('type', '0=安卓 1=网页', 0)]),
                '/login' => $get('邮箱登录', [$str('email', '邮箱', ''), $str('password', '密码', '')]),
            ],
        ];
    }
}
