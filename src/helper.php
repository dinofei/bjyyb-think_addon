<?php
declare(strict_types=1);

use think\helper\Str;

if (!function_exists('parse_config_file')) {
    /**
     * 解析配置文件
     * @param string $file
     * @return array|bool|mixed|null
     */
    function parse_config_file(string $file) {
        if (file_exists($file)) {
            $type   = pathinfo($file, PATHINFO_EXTENSION);
            switch ($type) {
                case 'php':
                    return include $file;
                case 'yml':
                case 'yaml':
                    if (function_exists('yaml_parse_file')) {
                        return yaml_parse_file($file);
                    }
                    break;
                case 'ini':
                    return parse_ini_file($file, true, INI_SCANNER_TYPED) ?: [];
                case 'json':
                    return json_decode(file_get_contents($file), true);
            }
        }
        return null;
    }
}

if (!function_exists('parse_entry_hook_docblock')) {
    /**
     * 解析插件入口钩子的注释块
     * @param string $entry
     * @param array $hooks
     * @return array
     * @throws ReflectionException
     */
    function parse_entry_hook_docblock(string $entry, array $hooks)
    {
        $hookDocBlock = [];
        $reflectCls = new \ReflectionClass($entry);
        foreach ($hooks as $hook) {
            $doc = $reflectCls->getMethod($hook)->getDocComment();
            if (false !== $doc) {
                preg_match('/(@title\(.*\))\s*.*(@description\(.*\))/', $doc, $matches);
                $annotation = [];
                foreach ($matches as $match) {
                    preg_match('/@(\w+)\((.*)\)/', $match, $anno);
                    $annotation[$anno[1]] = $anno[2];
                }
                $annotation['event'] = Str::snake($hook);
                $hookDocBlock[$annotation['event']] = $annotation;
            }
        }
        return $hookDocBlock;
    }
}

if (!function_exists('hook')) {
    /**
     * 触发插件钩子
     * @param string $event 钩子标识
     * @param null $param 参数
     * @param bool $once 只获取第一个结果
     * @return mixed
     */
    function hook(string $event, $param = null, bool $once = true) {
        return \think\facade\Event::trigger($event, $param, $once);
    }
}

if (!function_exists('rpc')) {
    /**
     * 实例插件对外服务类
     * @param string $name 插件标识.RPC标识
     * @param array $vars 参数
     * @return mixed
     */
    function rpc(string $name, array $vars = []) {
        return \think\addon\facade\Manager::rpc($name, $vars);
    }
}

if (!function_exists('aconfig')) {
    /**
     * 获取插件配置
     * @param string $addon 插件标识
     * @param string $type 配置类型 info 插件基本信息 config 插件通用配置
     * @return mixed|null
     */
    function aconfig(string $addon, string $type = 'info') {
        return \think\addon\facade\Manager::getSetting($addon, $type);
    }
}

if (!function_exists('aexec')) {
    /**
     * 执行插件方法
     * @param string $addon 插件标识
     * @param string $method 方法名
     * @return mixed|null
     */
    function aexec(string $addon, string $method = 'index') {
        return \think\addon\facade\Manager::exec($addon, $method);
    }
}

if (!function_exists('aget_permisstion_middleware')) {
    function aget_permisstion_middleware() {
        $alias = \think\facade\Config::get('addon.addon_permission_middleware_alias');
        return \think\facade\Config::get('middleware.alias.' . $alias, null) ?? \think\facade\Config::get('addon.addon_permission_middleware_default');
    }
}

