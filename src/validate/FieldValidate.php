<?php


namespace think\addon\validate;


class FieldValidate extends \think\Validate
{

    protected $rule = [
        'field' => 'require',
        'value' => 'require',
    ];

    protected $message = [
        'field' => '更新字段名必须',
        'value' => '更新值必须',
    ];

}