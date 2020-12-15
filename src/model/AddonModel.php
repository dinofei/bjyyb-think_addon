<?php
declare(strict_types=1);

namespace think\addon\model;


use think\addon\cache\HookCache;
use think\addon\facade\Manager;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\Model;

class AddonModel extends \think\Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $error;

    protected $type = [
        'expired_at' => 'timestamp',
        'closed_at' => 'timestamp',
        'uninstalled_at' => 'timestamp',
    ];

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->table = Config::get('addon.addon_db.addon');
    }

    public function addonInfo()
    {
        return $this->belongsTo(AddonInfoModel::class, 'info_id', 'id')->bind(['name']);
    }

    public function hook()
    {
        return $this->hasMany(HookModel::class, 'addon_id', 'id');
    }

    public function addAddon(array $addon): bool
    {
        Db::startTrans();
        try {
            $res = $this->save($this->convertAddon($addon));
            if ($res) {
                $hooks = Manager::getAddon($addon['name'])['hooks'] ?? false;
                if ($hooks) {
                    $res = $this->hook()->saveAll($hooks);
                }
            }
            if (false !== $res) {
                Db::commit();
                return true;
            }
            $this->error = '插件写入数据库失败001';
            Db::rollback();
        } catch (\Throwable $e) {
            $this->error = '插件写入数据库失败002，' . $e->getMessage();
            Db::rollback();
        }
        return false;
    }

    public function delAddon(int $id): bool
    {
        $addon = $this->with(['hook'])->find($id);
        $res = $addon->together(['hook'])->delete();
//        if (false !== $this->findOrFail($id)->save(['state' => 2, 'uninstalled_at' => $_SERVER['REQUEST_TIME']])) {
        if (false !== $res) {
            return true;
        }
        $this->error = '插件卸载失败000';
        return false;
    }

    protected function convertAddon(array $addon)
    {
        $data = [];
        $data['info_id'] = $addon['info_id'];
        $data['version'] = $addon['version'];
        $data['state'] = 1;
        $data['belong_to'] = $addon['belong_to'];
        $data['method'] = $addon['method'] ?? -1;
        $data['expired_at'] = $addon['pay_type'] == 0 ? 0 : strtotime('+1 month', $_SERVER['REQUEST_TIME']);
        return $data;
    }

    public function getError()
    {
        return $this->error;
    }

    public static function onAfterWrite(Model $model)
    {
        app()->make(HookCache::class)->delHooks(Request::instance()->belongTo);
    }

    public static function onAfterDelete(Model $model)
    {
        app()->make(HookCache::class)->delHooks(Request::instance()->belongTo);
    }

}