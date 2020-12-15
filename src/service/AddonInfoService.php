<?php

declare(strict_types=1);

namespace think\addon\service;


use think\Collection;
use think\facade\Request;
use think\addon\facade\Manager;
use think\addon\model\AddonInfoModel;
use think\paginator\driver\Bootstrap;

class AddonInfoService
{
    public function getWholeAddonInfo(bool $page = true, int $rows = 20)
    {
        $modules = Manager::getAddon();
        $installedColumnByName = (new AddonInfoModel())->getListGroupName();
        $installedName = array_keys($installedColumnByName);
        if ($page) {
            $total = count($modules);
            $currentPage = Request::get('page', 1);
            $items = array_slice($modules, ($currentPage - 1) * $rows, min($total, $rows));
            $collection = new Bootstrap(array_values($items), $rows, (int) $currentPage, $total);
        } else {
            $collection = new Collection(array_values($modules));
        }
        $collection->each(function ($value) use ($installedColumnByName, $installedName) {
            $key = $value['info']['name'];
            if (in_array($key, $installedName)) {
                $value['id'] = $installedColumnByName[$key]['id'];
                $value['open'] = 1;
                $value['created_at'] = $installedColumnByName[$key]['created_at'];
                $value['updated_at'] = $installedColumnByName[$key]['updated_at'];
                $value['app_version'] = $installedColumnByName[$key]['app_version'];
                $value['app_state'] = $installedColumnByName[$key]['app_state'];
                $value['description'] = $installedColumnByName[$key]['description'];
                $value['image'] = $installedColumnByName[$key]['image'];
                $value['icon'] = $installedColumnByName[$key]['icon'];
                $value['price'] = $installedColumnByName[$key]['price'];
                $value['pay_type'] = $installedColumnByName[$key]['pay_type'];
                $value['pay_count'] = $installedColumnByName[$key]['pay_count'];
                $value['install_count'] = $installedColumnByName[$key]['install_count'];
                $value['isUpdate'] = version_compare($installedColumnByName[$key]['app_version'], $value['info']['version']) < 0;
                $value['allowed_scene'] = $installedColumnByName[$key]['allowed_scene'];
            } else {
                $value['open'] = 0;
                $value['allowed_scene'] = '';
            }
            unset($value['service'], $value['entry']);
            return $value;
        });
        return $collection;
    }
}
