# CloudMusicAPI — 网易云音乐 API 代理服务

> 基于 Anon Framework Next 的网易云音乐接口代理：71 个接口统一 envelope 响应，扫码/验证码登录，附 SwaggerUI 文档。

## 模块边界

本服务是 **API 代理层**，不是网易云官方 SDK：

- 做什么：转发请求到网易云（weapi/eapi 加密），统一响应格式，透传登录态
- 不做什么：不存储用户数据、不做业务逻辑、不提供前端页面（首页仅是接口介绍）

| 边界 | 说明 |
|---|---|
| 代理目标 | `music.163.com`（weapi）+ `interfacepc.music.163.com`（eapi） |
| 加密方式 | `app/Service/Netease/Crypto.php`（AES-RSA 与网易云一致） |
| 登录态 | 经 `cookie` 参数透传，服务端不保存 |
| 文档入口 | `/docs`（SwaggerUI）、`/openapi.json`（OpenAPI 3.0.3） |

## 快速开始

```bash
composer install
php -S 127.0.0.1:8000 run/index.php
```

服务启动后：

- 首页介绍：`http://127.0.0.1:8000/`
- 接口文档：`http://127.0.0.1:8000/docs`
- OpenAPI 规范：`http://127.0.0.1:8000/openapi.json`

## 接口一览

全部接口挂在 `/api` 前缀下，GET/POST 均接受，统一走 `NeteaseProxy` 控制器。前端 external 模式把 baseURL 指到 `{host}/api` 即可。

### 播放 · 搜索 · 歌单

| 接口 | 说明 | 关键参数 |
|---|---|---|
| `/song/url` | 歌曲播放地址 | `id`（逗号分隔）、`br`（码率，默认 999000） |
| `/song/detail` | 歌曲详情 | `ids`（逗号分隔） |
| `/lyric` | 歌词 | `id` |
| `/cloudsearch` | 搜索 | `keywords`、`type`（1/10/100/1000）、`limit`、`offset` |
| `/playlist/detail` | 歌单详情 | `id`、`s`（最近收藏者数） |
| `/playlist/subscribe` | 收藏/取消歌单 | `id`、`t`（1=收藏 0=取消） |
| `/playlist/tracks` | 歌单加/删歌曲 | `op`（add/del）、`pid`、`tracks` |
| `/personalized` | 推荐歌单 | `limit` |
| `/recommend/songs` | 每日推荐（需登录） | `afresh` |

### 登录 · 用户

| 接口 | 说明 | 关键参数 |
|---|---|---|
| `/login/qr/key` | 二维码 key（登录第一步） | — |
| `/login/qr/create` | 生成二维码内容 | `key` |
| `/login/qr/check` | 轮询扫码状态 | `key` |
| `/captcha/sent` | 发送手机验证码 | `phone`、`ctcode`（默认 86） |
| `/login/cellphone` | 手机号登录 | `phone`、`captcha` 或 `password`、`countrycode` |
| `/user/account` | 当前登录账号 | 需登录态 |
| `/user/playlist` | 用户歌单 | `uid`、`limit`、`offset` |
| `/likelist` | 喜欢歌曲列表 | `uid` |
| `/like` | 红心/取消 | `id`、`like`（1=红心 0=取消） |

### 艺人 · 专辑

| 接口 | 说明 | 关键参数 |
|---|---|---|
| `/artists` | 歌手信息 | `id` |
| `/artist/album` | 歌手专辑 | `id`、`limit`、`offset` |
| `/artist/mv` | 歌手 MV | `id`、`limit`、`offset` |
| `/artist/desc` | 歌手介绍 | `id` |
| `/simi/artist` | 相似歌手 | `id` |
| `/album` | 专辑详情 | `id` |
| `/album/sublist` | 收藏的专辑 | `limit`、`offset` |
| `/album/sub` | 收藏/取消专辑 | `id`、`t` |

### 电台 · MV

| 接口 | 说明 | 关键参数 |
|---|---|---|
| `/dj/hot` | 热门电台 | `limit`、`offset` |
| `/dj/recommend` | 推荐电台 | — |
| `/dj/detail` | 电台详情 | `rid` |
| `/dj/program` | 电台节目列表 | `rid`、`limit`、`offset`、`asc` |
| `/dj/program/detail` | 节目详情 | `id` |
| `/dj/sublist` | 订阅的电台 | `limit`、`offset` |
| `/dj/sub` | 订阅/取消电台 | `rid`、`t` |
| `/mv/url` | MV 播放地址 | `id`、`r`（清晰度，默认 1080） |
| `/mv/detail` | MV 详情 | `id` |

### 喜欢 · 歌词

| 接口 | 说明 | 关键参数 |
|---|---|---|
| `/song/like/check` | 歌曲是否喜欢 | `ids`（逗号分隔） |
| `/lyric/new` | 新版歌词（含逐字） | `id` |

### 首页发现 · 榜单

| 接口 | 说明 | 关键参数 |
|---|---|---|
| `/banner` | 首页轮播图 | `type`（0=pc 1=android 2=iphone 3=ipad） |
| `/personalized/newsong` | 推荐新歌 | `limit`、`areaId` |
| `/personalized/djprogram` | 推荐电台节目 | — |
| `/personalized/mv` | 推荐 MV | — |
| `/toplist` | 所有榜单介绍 | — |
| `/toplist/detail` | 所有榜单内容摘要 | — |
| `/top/song` | 新歌速递 | `type`（0=全部 7=华语 96=欧美 8=日本 16=韩国） |
| `/top/album` | 新碟上架 | `area`（ALL/ZH/EA/KR/JP）、`limit`、`offset`、`year`、`month` |
| `/top/artists` | 热门歌手 | `limit`、`offset` |
| `/top/mv` | MV 排行 | `area`、`limit`、`offset` |
| `/top/playlist` | 分类歌单 | `cat`、`order`（hot/new）、`limit`、`offset` |
| `/search/hot` | 热门搜索 | — |
| `/search/suggest` | 搜索建议 | `keywords` |

### 相似 · 相关

| 接口 | 说明 | 关键参数 |
|---|---|---|
| `/simi/song` | 相似歌曲 | `id`、`limit`、`offset` |
| `/simi/playlist` | 相似歌单 | `id`、`limit`、`offset` |
| `/simi/mv` | 相似 MV | `mvid` |
| `/related/allvideo` | 相关视频 | `id` |

### 歌手增强 · MV 增强 · 私人FM

| 接口 | 说明 | 关键参数 |
|---|---|---|
| `/artist/songs` | 歌手全部歌曲 | `id`、`order`（hot/time）、`limit`、`offset` |
| `/artist/top/song` | 歌手热门 50 首 | `id` |
| `/artist/sublist` | 关注歌手列表 | `limit`、`offset` |
| `/artist/sub` | 收藏/取消收藏歌手 | `id`、`t` |
| `/mv/all` | 全部 MV | `area`、`type`、`order`、`limit`、`offset` |
| `/mv/first` | 最新 MV | `area`、`limit` |
| `/mv/sublist` | 收藏的 MV | `limit`、`offset` |
| `/mv/sub` | 收藏/取消收藏 MV | `mvid`、`t` |
| `/personal_fm` | 私人 FM | — |

### 歌单操作 · 用户中心

| 接口 | 说明 | 关键参数 |
|---|---|---|
| `/playlist/create` | 创建歌单 | `name`、`privacy`（0=公开 10=隐私）、`type` |
| `/playlist/delete` | 删除歌单 | `id` |
| `/login/status` | 登录状态 | 需登录态 |
| `/logout` | 退出登录 | 需登录态 |
| `/user/detail` | 用户详情 | `uid` |
| `/user/subcount` | 收藏计数 | 需登录态 |
| `/user/level` | 用户等级 | 需登录态 |
| `/user/record` | 听歌排行 | `uid`、`type`（0=所有时间 1=最近一周） |

## 统一响应格式（envelope）

所有接口返回 `{ success, code, message, data }` 结构，HTTP 状态码恒为 200：

```json
{
    "success": true,
    "code": 200,
    "message": "OK",
    "data": { "id": 33894312 },
    "cookie": "MUSIC_U=xxx; __csrf=yyy;"
}
```

| 字段 | 含义 |
|---|---|
| `success` | 是否业务成功。`code === 200` 为 true；二维码轮询的 `800/801/802/803` 是状态不是错误，也标 true |
| `code` | 网易云业务码（非 HTTP 状态码） |
| `message` | 网易云 `msg` / `message`，失败时给前端展示 |
| `data` | 网易云 data；无 data 字段的接口（如 `login/qr/key`）把业务字段装进 data |
| `cookie` | 仅登录类接口回传，前端写入后带 `cookie` 参数访问登录态接口 |

前端判断约定：`success === false` 即业务失败，抛错并展示 `message`；二维码轮询按 `code` 驱动 UI（800 过期 / 801 待扫 / 802 已扫 / 803 成功）。

## 登录流程

### 扫码登录

```bash
# 1. 取 key
curl "{host}/api/login/qr/key"
# → data.unikey

# 2. 生成二维码（返回 base64 png + qrurl）
curl "{host}/api/login/qr/create?key={unikey}"

# 3. 轮询状态，直到 803
curl "{host}/api/login/qr/check?key={unikey}"
# 803 时响应带 cookie，写入后即登录态
```

### 手机号登录

```bash
# 1. 发验证码
curl "{host}/api/captcha/sent?phone=13800000000"

# 2. 验证码登录
curl "{host}/api/login/cellphone?phone=13800000000&captcha=1234"
```

## 通用参数

| 参数 | 说明 |
|---|---|
| `cookie` | 登录凭证。登录后把接口返回的 `cookie` 值原样传回，即可访问需登录接口 |
| `realIP` | 客户端 IP，绕过网易云 IP 维度风控 |

### 风控规避（内置，无需配置）

| 项 | 策略 |
|---|---|
| 设备标识 `deviceId` | **动态生成**：`cookie` 里 `deviceId` 优先（前端持久化），否则临时文件 UUID 兜底。固定 id 多人共用会被网易云按设备维度风控 |
| User-Agent | 全端统一 `NeteaseMusicDesktop` 桌面 UA，与 eapi 的 `os: pc` 设备头匹配，避免「iPhone UA + Windows 设备头」的矛盾信号 |

## 代码结构

```text
CloudMusicAPI_PHP/
├── app/
│   ├── Controller/
│   │   ├── Index.php          # 首页介绍 + /docs + /openapi.json
│   │   └── NeteaseProxy.php   # /api/* 入口，统一 envelope
│   ├── Service/
│   │   ├── NeteaseApiClient.php  # 上游请求、接口表、weapi/eapi 加密、deviceId
│   │   └── Netease/Crypto.php    # AES-RSA 加密实现
│   └── Route/Main.php         # 路由定义（/api 组 + 首页三路由）
├── run/index.php              # 框架单一入口
├── anon.config.php            # 框架配置（缓存、CORS）
└── .env                       # 环境变量（密钥类配置）
```

接口表在 `NeteaseApiClient::ROUTES`，新增接口只需加一行路由 + 一行接口表 + 一个代理方法。

## 部署

- PHP 环境（≥ 8.1，需 `openssl` 扩展），`composer install` 装依赖
- nginx 把根指向 `run/`，伪静态转发到 `run/index.php`；或直接 `php -S` 跑内网
- 改代码后 **php-fpm 需 reload 清 opcache**，否则线上可能跑旧逻辑
- CORS 在 `anon.config.php` 的 `routing.cors` 配置

## 常见问题

| 症状 | 原因 | 排查 |
|---|---|---|
| `login/qr/check` 返回 400 参数错误 | 请求漏 `type: 3` 参数（网易云缺参即 400） | 确认请求带 key；对照 `buildData` 的 `login_qr_check => ['key', 'type' => 3]` |
| 登录接口时好时坏、触发风控 | deviceId 被多人共用（旧版固定 id） | 确认前端 `cookie` 恒带 `deviceId=xxx;`；检查服务端 deviceId 是否动态生成 |
| 接口 502 NCM_UPSTREAM_ERROR | 网易云拒绝/上游不可达 | 看 `NeteaseProxy` 捕获的异常；检查 UA 与 deviceId 是否统一 |
| 部署后行为与本地不一致 | opcache 缓存旧代码 | `php-fpm` reload 或重启容器 |

## 仓库

源码与更新：<https://github.com/YuiNijika/CloudMusicAPI>
