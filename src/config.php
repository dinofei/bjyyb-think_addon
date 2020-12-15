<?php

return [
    // 开启插件服务
    'enable' => true,
    // 是否检测用户插件权限
    'check_addon_permission' => true,
    // 用户授权中间件别名
    'addon_permission_middleware_alias' => 'auth',
    // 默认用户授权中间件
    'addon_permission_middleware_default' => \think\addon\middleware\AddonPermissionMiddleware::class,
    // 插件表默认表名
    'addon_db' => [
        'addon' => 'yyb_addon',
        'hook' => 'yyb_hook',
    ],
    // 默认缓存类型
    'cache_type' => 'redis',
];