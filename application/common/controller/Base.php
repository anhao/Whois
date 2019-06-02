<?php
/**
 * Copyright (c) 2019.
 * Author:Alone88
 * Github:https://github.com/anhao
 */

namespace app\common\controller;


use app\common\model\Config;
use think\Controller;
use think\facade\Session;
class Base extends Controller
{

    protected $conifg =[];


    public function initialize()
    {
        $config = Config::cache('config',600)->get(1)->toArray();
        $this->conifg=$config;
        $this->view->assign('config',$this->conifg);
    }

    //判断是否登录
    protected function isLogin(){
        if(!Session::has('user_id')){
            $this->error('您还没登录呢','/admin/login');
        }
    }
    protected function logined(){
        if(Session::has('user_id')){
            $this->error('您已经登录啦!','/admin/index');
        }
    }

}