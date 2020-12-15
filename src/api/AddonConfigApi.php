<?php


namespace think\addon\api;


use think\addon\model\AddonConfigModel;
use think\addon\validate\AddonConfigValidate;
use think\Request;

class AddonConfigApi extends \think\addon\AbstractApi
{
    public function save(Request $request)
    {
        validate(AddonConfigValidate::class)->check($request->post());
        return $this->api([], (new AddonConfigModel())->addConfig($request->post(), $request->belongTo));
    }
}