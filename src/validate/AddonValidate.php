<?php


namespace think\addon\validate;


use think\addon\model\AddonModel;

class AddonValidate extends \think\Validate
{
    protected $failException = true;

    protected $rule = [
        'id' => 'isNormal',
        'belong_to' => 'require',
        'info_id' => 'require',
        'name' => 'require',
        'version' => 'require|regex:^[1-9](\d+)*\.[0-9]+\.[0-9]+$',
        'type' => 'require|number',
    ];

    protected $message = [
        'info_id' => '插件ID必须',
        'name' => '插件标识必须',
        'version.require' => '版本必须',
        'version.regex' => '版本格式错误，例: 1.0.0',
        'belong_to' => '未设置插件所属',
    ];

    public function isNormal($value, $rule, $data, $field)
    {
        $addon = AddonModel::where(['id' => $value, 'belong_to' => $data['belong_to']])->find();
        if (is_null($addon)) {
            return '插件已卸载或不存在，卸载失败';
        }
        return true;
    }

    public function isExists($value, $rule, $data, $field)
    {
        $addon = AddonModel::where(['info_id' => $value, 'belong_to' => $data['belong_to']])->find();
        if (!is_null($addon)) {
            return '插件已经安装过了';
        }
        return true;
    }

    public function sceneInstall()
    {
        return $this->only(['info_id', 'name', 'version', 'belong_to'])->append('info_id', 'isExists');
    }

    public function sceneUninstall()
    {
        return $this->only(['id', 'name', 'belong_to']);
    }

}