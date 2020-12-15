<?php
declare(strict_types=1);

namespace think\addon;


use think\addon\facade\Manager;
use think\facade\Config;
use think\Request;

/**
 * 插件服务抽象类
 * Class AbstractService
 * @package think\addon
 */
abstract class AbstractService extends \think\Service
{
    /** @var string $basePath 插件根目录 */
    protected string $basePath;
    /** @var string $name 插件标识 */
    protected string $name;
    /** @var array $info 插件基本信息 */
    protected array $info;
    /** @var int $state 插件是否可用 0 禁用 1 正常 */
    protected int $state = 0;

    public function register()
    {
        if (Config::get('addon.enable')) {
            $this->setBasePath();
            $this->initialize();
        }
    }

    public function boot()
    {
        if ($this->state > 0) {
            $this->app->bind(static::class, $this);
            $this->registryAddonInfo();
            $this->registryAddonConfig();
            $this->registryAddonMiddleware();
            $this->registryAddonRpc();
            $this->registryAddonEvent();
            $this->registryAddonRoute();
            $hooks = $this->registryAddonHook();
            $this->registryManagerAddon($hooks);
        }
    }

    /**
     * 设置插件根目录
     * @return mixed
     */
    final protected function setBasePath()
    {
        $reflectCls = new \ReflectionClass(static::class);
        $this->basePath = dirname($reflectCls->getFileName());
    }

    protected function initialize()
    {
        if (file_exists(($infoFile = $this->basePath . '/info.ini'))) {
            $info = parse_config_file($infoFile);
            $mustContains = ['name', 'title', 'version', 'state'];
            if (count(array_intersect(array_keys($info), $mustContains)) == 4 ) {
                $this->setInfo($info);
                $this->setName($info['name']);
                $this->setState($info['state']);
            }
        }
    }

    /**
     * 注册插件基本信息
     */
    protected function registryAddonInfo()
    {
        Manager::setSetting($this->name, $this->info, 'info');
    }

    /**
     * 注册插件配置
     */
    protected function registryAddonConfig()
    {
        if (file_exists(($file = $this->basePath . '/config.php'))) {
            Manager::setSetting($this->name, parse_config_file($file), 'config');
        }
    }

    /**
     * 注册插件事件
     */
    protected function registryAddonEvent()
    {
        if (file_exists(($file = $this->basePath . '/event.php'))) {
            Manager::loadEvent($this->name, parse_config_file($file));
        }
    }

    /**
     * 注册插件路由
     */
    protected function registryAddonRoute()
    {
        if (file_exists(($file = $this->basePath . '/route.php'))) {
            Manager::loadRoute($this->name, $this->info['type'], $file);
        }
    }

    /**
     * 注册插件中间件配置
     */
    protected function registryAddonMiddleware()
    {
        if (file_exists(($file = $this->basePath . '/middleware.php'))) {
            Manager::loadMiddleware($this->name, parse_config_file($file));
        }
    }

    /**
     * 注册插件rpc调用类
     */
    protected function registryAddonRpc()
    {
        if (file_exists(($file = $this->basePath . '/rpc.php'))) {
            Manager::setSetting($this->name, parse_config_file($file), 'rpc');
        }
    }

    /**
     * 初始化插件钩子
     * @return array
     */
    protected function registryAddonHook()
    {
        /** @var AbstractEntry $entry */
        $entry = $this->app->make(str_replace('Service', 'Entry', static::class));
        $entry->initialize();
        return $entry->getHooks();
    }

    /**
     * 添加管理的插件
     * @param $hooks
     */
    protected function registryManagerAddon($hooks)
    {
        Manager::setAddon($this->name, [
            'hooks' => $hooks,
            'service' => static::class,
            'entry' => str_replace('Service', 'Entry', static::class),
            'info' => $this->info
        ]);
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param array $info
     */
    public function setInfo(array $info): void
    {
        $this->info = $info;
    }

    /**
     * @param int $state
     */
    public function setState(int $state): void
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

}