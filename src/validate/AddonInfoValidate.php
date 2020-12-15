<?php


namespace think\addon\validate;


class AddonInfoValidate extends \think\Validate
{

    protected $rule = [
        'title' => 'require',
        'name' => 'require|unique:yyb_addon_info',
        'image' => 'require',
        'type' => 'require',
        'price' => 'require|number',
        'pay_type' => 'require',
        'app_version' => 'require|regex:^[1-9](\d+)*\.[0-9]+\.[0-9]+$',
    ];

    protected $message = [
        'title' => '插件名称必须',
        'name.require' => '插件标识必须',
        'name.unique' => '已开启该插件',
        'image' => '插件缩略图必须',
        'type' => '插件类型必须',
        'price.require' => '价格必须',
        'price.number' => '价格格式错误',
        'pay_type' => '付费类型必须',
        'app_version.require' => '版本必须',
        'app_version.regex' => '版本格式错误，例: 1.0.0',
    ];

}