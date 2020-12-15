<?php

declare(strict_types=1);

namespace think\addon\service;


use think\Collection;
use think\facade\Request;
use think\addon\facade\Manager;
use think\addon\model\AddonModel;
use think\addon\model\AddonInfoModel;
use think\paginator\driver\Bootstrap;
use think\addon\exception\AddonException;
use think\addon\service\AddonChain\Handler;
use think\addon\service\AddonChain\SaveAddon;
use think\addon\service\AddonChain\InstallAddon;

class AddonService
{
    /**
     * 获取用户级别可用插件
     *
     * @param integer $belongTo
     * @param boolean $page
     * @param integer $rows
     * @param array $where
     * @param array $with
     * @return void
     * Author nf
     * Time 2020-12-10
     */
    public function getWholeAddon(int $belongTo, bool $page = true, int $rows = 20, array $where = [], array $with = [])
    {
        $modules = (new AddonInfoModel())->getOemList();
        $installed = $this->getUserInstallAddon($belongTo, false, $rows, $where, $with)->toArray();
        $installedColumnById = array_column($installed, null, 'info_id');
        $installed = array_keys($installedColumnById);
        if ($page) {
            $total = count($modules);
            $currentPage = Request::get('page', 1);
            $items = array_slice($modules, ($currentPage - 1) * $rows, min($total, $rows));
            $collection = new Bootstrap(array_values($items), $rows, (int) $currentPage, $total);
        } else {
            $collection = new Collection(array_values($modules));
        }
        $collection->each(function ($value) use ($installedColumnById, $installed) {
            $key = $value['id'];
            if (in_array($key, $installed)) {
                $value['isInstalled'] = 1;
                $value['info_id'] = $value['id'];
                $value['id'] = $installedColumnById[$key]['id'];
                $value['created_at'] = $installedColumnById[$key]['created_at'];
                $value['updated_at'] = $installedColumnById[$key]['updated_at'];
                $value['expired_at'] = $installedColumnById[$key]['expired_at'];
                $value['closed_at'] = $installedColumnById[$key]['closed_at'];
                $value['uninstalled_at'] = $installedColumnById[$key]['uninstalled_at'];
                $value['version'] = $installedColumnById[$key]['version'];
                $value['state'] = $installedColumnById[$key]['state'];
                $value['method'] = $installedColumnById[$key]['method'];
                $value['isUpdate'] = version_compare($installedColumnById[$key]['version'], $value['app_version']) < 0;
                $value['custom_enable'] = Manager::getSetting($value['name'] . '.custom_enable', 'config') ?? false;
                if ($value['custom_enable']) {
                    $value['custom_config'] = Manager::getSetting($value['name'] . '.custom_config', 'config') ?? [];
                }
            } else {
                $value['info_id'] = $value['id'];
                $value['isInstalled'] = 0;
            }
            return $value;
        });
        return $collection;
    }
    /**
     * 获取系统级别可用插件
     *
     * @param integer $belongTo
     * @param boolean $page
     * @param integer $rows
     * @param array $where
     * @param array $with
     * @return void
     * Author nf
     * Time 2020-12-10
     */
    public function getSysInstallAddon(int $belongTo = 0, bool $page = true, int $rows = 20, array $where = [], array $with = [])
    {
        $dbQuery = (new AddonModel())->hasWhere('addonInfo', ['app_state' => 1, 'type' => 0])
            ->with($with)
            ->where($where)
            ->where('belong_to', $belongTo);
        if ($page) {
            return $dbQuery->paginate($rows);
        }
        return $dbQuery->select();
    }
    /**
     * 获取oem用户已安装插件
     *
     * @param integer $belongTo
     * @param boolean $page
     * @param integer $rows
     * @param array $where
     * @param array $with
     * @return void
     * Author nf
     * Time 2020-12-10
     */
    public function getUserInstallAddon(int $belongTo, bool $page = true, int $rows = 20, $where = [], array $with = [])
    {
        $dbQuery = (new AddonModel())->hasWhere('addonInfo', ['app_state' => 1, 'type' => 1])
            ->with($with)
            ->where($where)
            ->where('belong_to', $belongTo);
        if ($page) {
            return $dbQuery->paginate($rows);
        }
        return $dbQuery->select();
    }
    /**
     * cms用户可用的插件
     *
     * @param string $scene
     * @param boolean $page
     * @param integer $rows
     * @param array $where
     * @param array $with
     * @return void
     * Author nf
     * Time 2020-12-08
     */
    public function getCmsUserAddon(string $scene, bool $page = true, int $rows = 20, $where = [], array $with = [])
    {
        $dbQuery = (new AddonInfoModel())
            ->db()
            ->when($scene, function ($query) use ($scene) {
                $query->where('allowed_scene', 'in', $scene);
            })
            ->with($with)
            ->where($where);
        if ($page) {
            return $dbQuery->paginate($rows);
        }
        return $dbQuery->select();
    }

    public function hasInstalledAddon(int $belongTo, string $addonName): bool
    {
        return (new AddonModel())->hasWhere('addonInfo', ['name' => $addonName])->where('belong_to', $belongTo)->where('state', 1)->count() > 0;
    }

    public function install(array $data)
    {
        $module = $this->checkAddon($data['name']);
        $handler = new Handler(new SaveAddon(), new InstallAddon());
        if ($handler->install($module, $data)) {
            AddonInfoModel::incrInstallByName($data['name']);
            return true;
        }
        return $handler->getError();
    }

    public function uninstall(array $data)
    {
        $module = $this->checkAddon($data['name']);
        $handler = new Handler(new SaveAddon(), new InstallAddon());
        if ($handler->uninstall($module, $data)) {
            return true;
        }
        return $handler->getError();
    }

    public function toggle(int $id, int $belongTo, $state)
    {
        if (!is_null($addon = AddonModel::where(['id' => $id, 'belong_to' => $belongTo])->find())) {
            $data['state'] = $state;
            if ($state == 0) {
                $data['closed_at'] = $_SERVER['REQUEST_TIME'];
            }
            return false !== $addon->save($data);
        }
        return false;
    }

    protected function checkAddon($name)
    {
        if (is_null($module = Manager::getAddon($name))) {
            throw new AddonException('插件不存在');
        }
        return $module;
    }
}
