<?php
declare(strict_types=1);

namespace think\addon\service\AddonChain;


class InstallAddon implements HandleInterface
{
    protected $error;

    public function install($addon, $data): bool
    {
        try {
            $res = call_user_func([app()->get($addon['entry']), 'install']);
            if ($res) {
                return true;
            }
            $this->error = '插件安装失败001';
        } catch (\Throwable $e) {
            $this->error = '插件安装失败002, ' . $e->getMessage();
        }
        return false;
    }

    public function uninstall($addon, $data): bool
    {
        try {
            $res = call_user_func([app()->get($addon['entry']), 'uninstall']);
            if ($res) {
                return true;
            }
            $this->error = '插件卸载失败001';
        } catch (\Throwable $e) {
            $this->error = '插件卸载失败002, ' . $e->getMessage();
        }
        return false;
    }

    public function getError(): string
    {
        return $this->error;
    }
}