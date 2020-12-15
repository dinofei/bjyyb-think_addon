<?php
declare(strict_types=1);

namespace think\addon;

use think\App;
use think\facade\Event;
use think\helper\Str;

/**
 * 钩子入口抽象类
 * Class AbstractEntry
 * @package think\addon
 */
abstract class AbstractEntry
{
    protected App $app;
    /** @var AbstractService $service */
    protected $service;
    /** @var array $hooks 钩子集合 */
    protected array $hooks = [];
    /** @var array $ignore 排除加入钩子的方法 */
    protected array $ignore = [];
    protected bool $initialized = false;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->service = $this->app->get(str_replace('Entry', 'Service', static::class));
    }

    public function initialize()
    {
        if (!$this->initialized) {
            $this->initHook();
            $this->initialized = true;
        }
    }

    /**
     * 初始化插件钩子
     */
    protected function initHook()
    {
        $hooks = array_diff(get_class_methods(static::class), get_class_methods(self::class), $this->ignore);
        $this->hooks = parse_entry_hook_docblock(static::class, $hooks);
    }

    /**
     * 注册钩子监听事件
     */
    public function registerHook()
    {
        $listen = [];
        foreach ($this->hooks as $item) {
            $listen['addon.' . $this->service->getName() . '.' . $item['event']] = [[static::class, Str::camel($item['event'])]];
        }
        !empty($listen) && Event::listenEvents($listen);
    }

    /**
     * @return array
     */
    public function getHooks(): array
    {
        return $this->hooks;
    }

    /**
     * 安装插件
     * @return bool
     */
    abstract public function install(): bool;

    /**
     * 卸载插件
     * @return bool
     */
    abstract public function uninstall(): bool;

    /**
     * 插件介绍
     * @return mixed
     */
    abstract public function index();
}