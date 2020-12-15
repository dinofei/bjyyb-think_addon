<?php
declare(strict_types=1);

namespace think\addon\api;


use think\addon\model\AddonInfoModel;
use think\addon\service\AddonInfoService;
use think\addon\validate\AddonInfoValidate;
use think\addon\validate\FieldValidate;
use think\Request;

class AddonInfoApi extends \think\addon\AbstractApi
{

    public function index()
    {
        return $this->api($this->formatPaginator((new AddonInfoService())->getWholeAddonInfo()));
    }

    public function add(Request $request)
    {
        $input = $request->param();
        validate(AddonInfoValidate::class)->check($input);
        return $this->api([], (new AddonInfoModel())->add($input));
    }

    public function info(int $id)
    {
        return $this->api((new AddonInfoModel())->findOrFail($id)->toArray());
    }

    public function update(int $id, Request $request)
    {
        $input = $request->param();
        validate(AddonInfoValidate::class)->check($input);
        return $this->api([], (new AddonInfoModel())->edit($id, $input));
    }

    public function delete(int $id)
    {
        return $this->api([], (new AddonInfoModel())->findOrFail($id)->delete());
    }

    public function field(int $id, Request $request)
    {
        $input = $request->param();
        validate(FieldValidate::class)->check($input);
        return $this->api([], (new AddonInfoModel())->updateField($id, $input['field'], $input['value']));
    }

}