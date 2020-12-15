<?php


namespace think\addon\model;


class AddonConfigModel extends \think\Model
{
    protected $table = 'yyb_addon_config';
    protected $json = ['content'];
    protected $jsonAssoc = true;

    public function addConfig(array $data, $belongTo)
    {
        if (!is_null($info = $this->where('belong_to', $belongTo)->where('name', $data['name'])->find())) {
            return $info->save(['content' => (array) $data['content']]);
        }
        return $this->save(['belong_to' => $belongTo, 'name' => $data['name'], 'content' => (array) $data['content']]);
    }
}