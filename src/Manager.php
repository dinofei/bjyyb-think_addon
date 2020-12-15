<?php
declare(strict_types=1);

namespace think\addon;

use think\addon\cache\HookCache;
use think\addon\exception\AddonException;
use think\App;
use think\event\RouteLoaded;
use think\facade\Config;
use think\facade\Request;
use think\helper\Str;

/**
 * 插件管理者
 * Class Manager
 * @package think\addon
 */
class Manager
{
    protected App $app;
    protected array $addon;
    protected array $notAllowExecMethod = ['install', 'uninstall'];

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * 添加插件
     * @param string $key
     * @param array $addon
     */
    public function setAddon(string $key, array $addon): void
    {
        if (isset($this->addon[$key])) {
            $this->addon[$key] = array_merge($this->addon[$key], $addon);
        } else {
            $this->addon[$key] = $addon;
        }
    }

    /**
     * 获取插件
     * @param null|string $key
     * @return null|array
     */
    public function getAddon(?string $key = null): ?array
    {
        if (is_null($key)) {
            return $this->addon;
        }
        return $this->addon[$key] ?? null;
    }

    /**
     * 插件是否存在
     * @param string $key
     * @return bool
     */
    public function hasAddon(string $key): bool
    {
        return isset($this->addon[$key]);
    }

    /**
     * 遍历插件目录获取类的集合
     * @param string $className
     * @return array
     */
    public function globPathWithClass(string $className)
    {
        $classMap = parse_config_file($this->app->getRootPath() . 'vendor/composer/autoload_classmap.php');
        $pattern = sprintf('/^Addon.*%s/', $className);
        return array_keys(array_filter($classMap, function ($item) use ($pattern) {
            $res = preg_match($pattern, $item, $match);
            return $res && is_subclass_of($match[0], AbstractService::class);
        }, ARRAY_FILTER_USE_KEY));
    }

    /**
     * 读取插件设置信息
     * @param string $addon
     * @param string $type
     * @return mixed|null
     */
    public function getSetting(string $addon, string $type = 'info')
    {
        return Config::get('addon@' . $type . "." . $addon) ?? null;
    }

    /**
     * 更新插件设置信息
     * @param string $addon
     * @param array $value
     * @param string $type
     */
    public function setSetting(string $addon, array $value, string $type = 'info')
    {
        Config::set([
            $addon => $value
        ], 'addon@' . $type);
    }

    /**
     * 加载插件自定义事件
     * @param string $name
     * @param array $events
     */
    public function loadEvent(string $name, array $events = [])
    {
        foreach ($events as $key => $event) {
            if ($key == 'bind' || $key == 'listen') {
                $map = [];
                foreach ($event as $kk => $item) {
                    $map[sprintf('addon.event.%s.%s', $name, $kk)] = $item;
                }
                $events[$key] = $map;
            } else {
                unset($events[$key]);
            }
        }
        $this->app->loadEvent($events);
    }

    /**
     * 加载插件自定义路由
     * @param string $name
     * @param int $type
     * @param string $file
     */
    public function loadRoute(string $name, int $type, string $file)
    {
        $prefix = 'addon/' . $name;
        $this->app->event->listen(RouteLoaded::class, function () use ($file, $prefix, $name, $type) {
            $route = $this->app->route->group($prefix, function () use ($file) {
                parse_config_file($file);
            });
            if ($type != 0) {
                $route->middleware(aget_permisstion_middleware())->middleware(function (\think\Request $request, \Closure $next) use ($name) {
                    Manager::checkAuth($name);
                    return $next($request);
                });
            }
        });
    }

    /**
     * 加载中间件配置
     * @param string $name
     * @param array $middleware
     */
    public function loadMiddleware(string $name, array $middleware)
    {
        if (isset($middleware['alias'])) {
            $isLoadedMiddleware = $this->app->config->get('middleware', []);
            $alias = $isLoadedMiddleware['alias'] ?? [];
            $map = [];
            foreach ($middleware['alias'] as $kk => $item) {
                $map[sprintf('addon.middleware.%s.%s', $name, $kk)] = $item;
            }
            $isLoadedMiddleware['alias'] = array_merge($alias, $map);
            $this->app->config->set($isLoadedMiddleware, 'middleware');
        }
        $this->importMiddleware($name, $middleware, 'global');
        $this->importMiddleware($name, $middleware, 'route');
    }

    /**
     * 导入全局和路由中间件
     * @param string $name
     * @param array $middleware
     * @param string $type
     */
    protected function importMiddleware(string $name, array $middleware, string $type)
    {
        if (isset($middleware[$type])) {
            $map = [];
            foreach ($middleware[$type] as $item) {
                $map[] = strpos($item, '\\') !== false ? $item : sprintf('addon.middleware.%s.%s', $name, $item);
            }
            $this->app->middleware->import($map, $type);
        }
    }

    /**
     * 执行插件方法
     * @param $key
     * @param string $method
     * @return mixed
     */
    public function exec(string $key, $method = 'index')
    {
        $this->checkAuth($key);
        $entry = $this->app->make($this->getAddon($key)['entry']);
        if (!method_exists($entry, $method) || in_array($method, $this->notAllowExecMethod)) {
            throw new AddonException('插件方法不存在');
        }
        return $this->app->invokeMethod([$entry, $method]);
    }

    /**
     * 获取插件对外服务类
     * @param string $name
     * @param array $vars
     * @return mixed
     */
    public function rpc(string $name, array $vars = [])
    {
        if (strpos($name, '.') === false) {
            throw new AddonException('rpc命名格式错误');
        }
        [$addon, $bind] = explode('.', $name);
        $this->checkAuth($addon);
        $rpc = $this->getSetting($addon, 'rpc')[$bind] ?? null;
        if (is_null($rpc) || !is_subclass_of($rpc, AbstractRpc::class)) {
            throw new AddonException('未找到rpc服务类');
        }
        return $this->app->make($rpc, $vars, true);
    }

    /**
     * 监听钩子事件
     * @param array $hooks
     */
    public function listenHook(array $hooks)
    {
        if (!empty($hooks)) {
            foreach ($hooks as $item) {
                if (!empty($item['hook'])) {
                    $methods = array_column($item['hook'], 'event');
                    $entry = self::getAddon($item['name'])['entry'] ?? false;
                    if ($entry) {
                        array_walk($methods, function (&$item) {
                            $item = Str::camel($item);
                        });
                        $this->app->get($entry)->registerHook($methods);
                    }
                }
            }
        }
    }

    /**
     * 检验用户插件权限
     * @param string $key
     * @return bool
     */
    public function checkAuth(string $key)
    {
        if (is_null($addon = $this->getAddon($key))) {
            throw new AddonException('插件不存在');
        }
        if ($addon['info']['type'] == 1 && Config::get('addon.check_addon_permission')) {
            $addon = $this->app->make(HookCache::class)->getUserHooks(Request::instance()->belongTo);
            if (!in_array($key, array_column($addon, 'name'))) {
                throw new AddonException('权限不足，无法使用该插件功能');
            }
        }
        return true;
    }
    
}