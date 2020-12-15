<?php


namespace think\addon\service;


use think\addon\model\HookModel;

class HookService
{

    public function hookList(int $belongTo = 0, bool $page = true, int $rows = 20, array $where = [], array $with = [])
    {
        $collection = $this->getHook($belongTo, $page, $rows, $where, $with);
        return $collection->each(function ($item) {
            $item['event'] = 'addon.' . $item['addon']['name'] . '.' . $item['event'];
            unset($item['addon']);
            return $item;
        });
    }

    public function getHook(int $belongTo = 0, bool $page = true, int $rows = 20, array $where = [], array $with = [])
    {
        $dbQuery = (new HookModel())->db()->with($with)->where('belong_to', $belongTo)->where($where);
        if ($page) {
            return $dbQuery->paginate($rows);
        }
        return $dbQuery->select();
    }

    public function toggle(int $id, int $belongTo, $state)
    {
        if (!is_null($hook = HookModel::where(['id' => $id, 'belong_to' => $belongTo])->find())) {
            return false !== $hook->save(['state' => $state]);
        }
        return false;
    }

}