<?php


namespace think\addon\facade;

/**
 * @mixin \think\addon\Manager
 * @method static mixed|null getSetting(string $module, string $type = 'info')
 * @method static void setSetting(string $module, array $value, string $type = 'info')
 * @method static void setAddon(string $key, array $module)
 * @method static null|array getAddon(?string $key = null)
 * @method static bool hasAddon(string $key)
 * @method static array globPathWithClass(string $className)
 * @method static mixed exec(string $key, $method = 'index')
 * @method static mixed rpc(string $name, array $vars = [])
 * @method static mixed listenHook(array $hooks)
 * @method static mixed loadEvent(string $name, array $events = [])
 * @method static mixed loadRoute(string $name, int $type, string $file)
 * @method static mixed loadMiddleware(string $name, array $middleware)
 * @method static mixed checkAuth(string $key)
 */
class Manager extends \think\Facade
{
    protected static function getFacadeClass()
    {
        return 'addon_manager';
    }
}