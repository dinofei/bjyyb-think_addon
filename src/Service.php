<?php
declare(strict_types=1);

namespace think\addon;

use think\event\RouteLoaded;
use think\facade\Config;

class Service extends \think\Service
{
    public function register()
    {
        $this->app->bind('addon_manager', Manager::class);
    }

    public function boot()
    {
        if (Config::get('addon.enable')) {
            $this->loadRoute();
            $this->loadEvent();
        }
        $this->registerCommand();
    }

    protected function registerCommand()
    {
        $command = include __DIR__ . '/command.php';
        $this->commands($command);
    }

    protected function loadRoute()
    {
        $this->app->event->listen(RouteLoaded::class, function () {
            include __DIR__ . '/route.php';
        });
    }

    protected function loadEvent()
    {
        $events = include __DIR__ . '/event.php';
        $this->app->event->subscribe($events['subscribe'] ?? []);
    }

}