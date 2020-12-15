<?php
declare(strict_types=1);

namespace think\addon\service\AddonChain;


use think\addon\model\AddonModel;

class SaveAddon implements HandleInterface
{
    protected $error;

    public function install($addon, $data): bool
    {
        $addonModel = new AddonModel();
        if ($addonModel->addAddon($data)) {
            return true;
        }
        $this->error = $addonModel->getError();
        return false;
    }

    public function uninstall($addon, $data): bool
    {
        $addonModel = new AddonModel();
        if ($addonModel->delAddon((int) $data['id'])) {
            return true;
        }
        $this->error = $addonModel->getError();
        return false;
    }

    public function getError(): string
    {
        return $this->error;
    }
}