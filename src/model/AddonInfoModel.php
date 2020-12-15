<?php

declare(strict_types=1);

namespace think\addon\model;


class AddonInfoModel extends \think\Model
{
    protected $table = 'yyb_addon_info';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    public function detail()
    {
        return $this->hasOne(AddonInfoDetailModel::class, 'info_id', 'id');
    }

    public function getOemList(): array
    {
        return $this->where(['app_state' => 1, 'type' => 1])->field('id,title,name,description,app_version,app_state,image,index_url,price,pay_type,allowed_scene')->select()->toArray();
    }

    public function getOrFail(int $id): array
    {
        $addon = $this->find($id);
        if (is_null($addon)) {
            throw new \RuntimeException('插件不存在');
        }
        return $addon->toArray();
    }

    public function getListGroupName(): array
    {
        return array_column($this->select()->toArray(), null, 'name');
    }

    public function add(array $data): bool
    {
        $allowedScene = $data['allowed_scene'] ?? '';
        if (is_array($allowedScene)) {
            $allowedScene = implode(',', $allowedScene);
        }
        $this->set('title', $data['title']);
        $this->set('name', $data['name']);
        $this->set('description', $data['description'] ?? '');
        $this->set('app_version', $data['app_version']);
        $this->set('type', $data['type']);
        $this->set('app_state', $data['app_state'] ?? 1);
        $this->set('image', $data['image']);
        $this->set('index_url', $data['index_url'] ?? '');
        $this->set('icon', $data['icon'] ?? '');
        $this->set('price', $data['price']);
        $this->set('pay_type', $data['pay_type']);
        $this->set('allowed_scene', $allowedScene);
        return $this->save();
    }

    public function edit(int $id, array $data): bool
    {
        $info = $this->findOrFail($id);
        $allowedScene = $data['allowed_scene'] ?? $info['allowed_scene'];
        if (is_array($allowedScene)) {
            $allowedScene = implode(',', $allowedScene);
        }
        $info->set('title', $data['title']);
        $info->set('name', $data['name']);
        $info->set('description', $data['description'] ?? $info['description']);
        $info->set('app_version', $data['app_version']);
        $info->set('type', $data['type']);
        $info->set('app_state', $data['app_state'] ?? $info['app_state']);
        $info->set('image', $data['image']);
        $info->set('icon', $data['icon'] ?? $info['icon']);
        $info->set('price', $data['price']);
        $info->set('pay_type', $data['pay_type']);
        $this->set('allowed_scene', $allowedScene);
        return $info->save();
    }

    public function updateField(int $id, string $field, $value): bool
    {
        return $this->findOrFail($id)->save([$field => $value]);
    }

    public static function incrInstallByName(string $name)
    {
        (new static())->db()->where('name', $name)->inc('install_count');
    }

    public static function incrPayByName(string $name)
    {
        (new static())->db()->where('name', $name)->inc('pay_count');
    }
}
