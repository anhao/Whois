<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------





//首页路由
Route::get('/','index');

//后台路由
//Route::get('/admin','admin/index/index');

Route::alias('admin','admin/Index');

//Route::alias('admin/logout','admin/Index/logout');


//域名正则
Route::pattern([
    'domain' => '(.*)+',
]);

//验证码
Route::any('verify','index/verify');

//更新缓存
Route::any('up_cache','index/refreshCache');
// 关于我们
Route::any('about','about/index');
//联系
Route::any('contact','contact/index');
// 友情链接
Route::any('links','links/index');

//域名查询
Route::any('/:domain','@index/index/query?domain=:domain');




return [

];
