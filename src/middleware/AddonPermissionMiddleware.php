<?php


namespace think\addon\middleware;


use think\Request;

class AddonPermissionMiddleware extends \think\Middleware
{

    public function handle(Request $request, \Closure $next)
    {
        $request->belongTo = $request->get('uid', 0);
        return $next($request);
    }

}