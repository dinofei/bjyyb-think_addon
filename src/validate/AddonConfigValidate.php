<?php


namespace think\addon\validate;


class AddonConfigValidate extends \think\Validate
{

    protected $rule = [
        'name' => 'require',
        'content' => 'require',
    ];

    protected $message = [
        'name' => '插件标识必须',
        'content' => '配置内容必须',
    ];
}