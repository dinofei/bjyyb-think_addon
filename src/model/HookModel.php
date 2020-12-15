<?php
declare(strict_types=1);

namespace think\addon\model;

use think\addon\cache\HookCache;
use think\facade\Config;
use think\facade\Request;
use \think\Model;

class HookModel extends \think\Model
{
    protected $autoWriteTimestamp = false;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->table = Config::get('addon.addon_db.hook');
    }

    public function addon()
    {
        return $this->belongsTo(AddonModel::class, 'addon_id', 'id');
    }

    public static function onBeforeInsert(Model $model)
    {
        $model->belong_to = Request::instance()->belongTo;
        return true;
    }

    protected function convert(array $addon)
    {
        $data = [];
        $data['title'] = $addon['title'];
        $data['description'] = $addon['description'] ?? '';
        $data['name'] = $addon['name'];
        $data['version'] = $addon['version'];
        $data['state'] = $addon['state'];
        $data['image'] = $addon['image'] ?? '';
        $data['icon'] = $addon['icon'] ?? '';
        $data['belong_to'] = $addon['belong_to'];
        return $data;
    }

    public static function onAfterWrite(Model $model)
    {
        app()->make(HookCache::class)->delHooks(Request::instance()->belongTo);
    }
}