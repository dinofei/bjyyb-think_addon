<?php


namespace think\addon;


use think\App;
use think\facade\Request;
use think\facade\View;


class AddonBaseController
{
    protected $app;
    protected $viewPath;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->initialize();
    }

    protected function initialize()
    {
        $this->setViewPath();
        View::config(['view_path' => $this->viewPath, 'view_suffix' => 'html', 'tpl_replace_string'  =>  [
            '__PUBLIC__'=> '/static/addon/',
        ]]);
        View::assign('webtoken', Request::get('webtoken', null));
    }

    final protected function setViewPath()
    {
        $reflectCls = new \ReflectionClass(static::class);
        $this->viewPath = dirname($reflectCls->getFileName()) . '/../view/';
    }

}