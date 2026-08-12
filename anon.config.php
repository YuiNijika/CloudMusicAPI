<?php

use Anon\Core\Facade\Env;
use Anon\Core\Support\Config;

/**
 * Anon Framework Next 核心配置文件
 *
 * 仅放框架行为覆盖。业务模型清单见 app/Ai/catalog.php；密钥见 .env*。
 *
 * @see https://anon.miomoe.cn/guide/architecture/configuration
 */

return Config::define([
    'cache' => [
        // 无 phpredis 扩展时自动降级文件缓存，避免 Cache 初始化直接 500
        'default' => extension_loaded('redis') ? (string) Env::get('CACHE_DRIVER', 'redis') : 'file',
    ],

    'http' => [
        'ssl_verify' => false,
    ],

    'routing' => [
        'cors' => [
            'origin' => ($allowOrigins = trim((string) Env::get('CORS_ALLOW_ORIGINS', '*'))) === '*'
                ? '*'
                : array_values(array_filter(array_map('trim', explode(',', $allowOrigins)))),
            'allow_headers' => array_values(array_filter(array_map(
                'trim',
                // 兼容 OpenAI 客户端常见头
                explode(',', (string) Env::get(
                    'CORS_ALLOW_HEADERS',
                    'Content-Type, Authorization, X-Requested-With, Accept, X-Login-Id, X-Api-Key, api-key'
                ))
            ))),
            'methods' => array_values(array_filter(array_map(
                'trim',
                explode(',', (string) Env::get('CORS_ALLOW_METHODS', 'GET, POST, PUT, DELETE, PATCH, OPTIONS'))
            ))),
            'credentials' => $allowOrigins !== '*',
            'max_age' => max(0, (int) Env::get('CORS_MAX_AGE', 86400)),
        ],
    ],
]);