<?php
use think\facade\Route;

Route::group('addon', function () {
    Route::get('', '\think\addon\api\AddonApi@index');
    Route::get('addon_list', '\think\addon\api\AddonApi@addonList');
    Route::get('hook_list', '\think\addon\api\AddonApi@hookList');
    Route::post('add_addon', '\think\addon\api\AddonApi@addAddon');
    Route::post('set_addon_config', '\think\addon\api\AddonConfigApi@save');
    Route::delete('del_addon', '\think\addon\api\AddonApi@delAddon');
    Route::put('toggle_addon_state/:id', '\think\addon\api\AddonApi@toggleAddonState');
    Route::put('toggle_hook_state/:id', '\think\addon\api\AddonApi@toggleHookState');
})->middleware([aget_permisstion_middleware()]);

Route::group('addon_info', function () {
    Route::get('', '\think\addon\api\AddonInfoApi@index');
    Route::post('', '\think\addon\api\AddonInfoApi@add');
    Route::post('field/:id', '\think\addon\api\AddonInfoApi@field');
    Route::delete(':id', '\think\addon\api\AddonInfoApi@delete');
    Route::get(':id', '\think\addon\api\AddonInfoApi@info');
    Route::put(':id', '\think\addon\api\AddonInfoApi@update');
})->middleware([aget_permisstion_middleware()]);