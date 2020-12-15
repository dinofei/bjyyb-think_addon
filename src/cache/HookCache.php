<?php


namespace think\addon\cache;


use think\Config;
use think\facade\Cache;
use think\addon\service\AddonService;

class HookCache
{
    protected $cache;

    public function __construct(Config $config)
    {
        $this->cache = Cache::store($config->get('addon.cache_type'));
    }

    public function getSysHooks()
    {
        $key = 'hook_0';
        if (!$this->cache->has($key)) {
            $hooks = (new AddonService())->getSysInstallAddon(0, false, 0, ['state' => 1], [
                'hook' => function ($sql) {
                    $sql->where(['state' => 1]);
                },
                'addonInfo',
            ])->toArray();
            $this->cache->set($key, $hooks);
        }
        return $this->cache->get($key);
    }

    public function getUserHooks($belongTo = null)
    {
        if (is_null($belongTo)) {
            return [];
        }
        $key = 'hook_' . $belongTo;
        if (!$this->cache->has($key)) {
            $hooks = (new AddonService())->getUserInstallAddon($belongTo, false, 0, function ($query) {
                $query->where('state', 1)->where(function ($query) {
                    $query->where('expired_at', 0)->whereOr('expired_at', '>=', $_SERVER['REQUEST_TIME']);
                });
            }, [
                'hook' => function ($sql) {
                    $sql->where(['state' => 1]);
                },
                'addonInfo',
            ])->toArray();
            $this->cache->set($key, $hooks);
        }
        return $this->cache->get($key);
    }

    public function delHooks($belongTo = null)
    {
        $this->cache->delete('hook_' . $belongTo);
    }

}