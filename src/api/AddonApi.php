<?php
declare(strict_types=1);

namespace think\addon\api;


use think\addon\model\AddonInfoModel;
use think\addon\service\AddonService;
use think\addon\service\HookService;
use think\addon\validate\AddonValidate;
use think\Request;

class AddonApi extends \think\addon\AbstractApi
{
    public function index(Request $request)
    {
    }

    public function addonList(Request $request)
    {
        return $this->api($this->formatPaginator((new AddonService())->getWholeAddon($request->belongTo)));
    }

    public function hookList(Request $request)
    {
        return $this->api($this->formatPaginator((new HookService())->hookList($request->belongTo, true, 20, [], ['addon'])));
    }

    public function addAddon(Request $request)
    {
        $addon = (new AddonInfoModel())->getOrFail($request->param('info_id', 0));
        if ($addon['pay_type'] > 0 && $addon['price'] > 0) {
            return $this->error('付费使用通道暂未开通');
        }
        $addon['belong_to'] = $request->belongTo;
        $addon['info_id'] = $request->param('info_id');
        $addon['version'] = $addon['app_version'];
        validate(AddonValidate::class . '.install')->check($addon);
        return $this->api([], (new AddonService())->install($addon));
    }

    public function delAddon(Request $request)
    {
        $input = [
            'name' => $request->param('name'),
            'belong_to' => $request->belongTo,
            'id' => $request->param('id')
        ];
        validate(AddonValidate::class . '.uninstall')->check($input);
        return $this->api([], (new AddonService())->uninstall($input));
    }

    public function toggleAddonState(int $id, Request $request)
    {
        return $this->api([], (new AddonService())->toggle($id, $request->belongTo, $request->put('state', 1)));
    }

    public function toggleHookState(int $id, Request $request)
    {
        return $this->api([], (new HookService())->toggle($id, $request->belongTo, $request->put('state', 1)));
    }
}