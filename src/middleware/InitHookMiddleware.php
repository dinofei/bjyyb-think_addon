<?php


namespace think\addon\middleware;


use think\facade\Event;
use think\Request;

class InitHookMiddleware extends \think\Middleware
{

    public function handle(Request $request, \Closure $next)
    {
        Event::trigger('SysHook');
        return $next($request);
    }
    
}