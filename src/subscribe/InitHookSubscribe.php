<?php


namespace think\addon\subscribe;


use think\addon\cache\HookCache;
use think\addon\facade\Manager;
use think\facade\Request;

class InitHookSubscribe
{
    public function onSysHook()
    {
        Manager::listenHook(app()->make(HookCache::class)->getSysHooks());
    }

    public function onUserHook(?int $belongTo = 0)
    {
        if (!is_null($belongTo)) {
            Request::instance()->belongTo = $belongTo;
            Manager::listenHook(app()->make(HookCache::class)->getUserHooks($belongTo));
        }
    }
}