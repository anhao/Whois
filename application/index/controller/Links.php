<?php
/**
 * Copyright (c) 2019.
 * Author:Alone88
 * Github:https://github.com/anhao
 */

namespace app\index\controller;


use app\common\controller\Base;

class Links extends Base
{

    /**
     * 友情链接
     */
    public function index(){

        $links = \app\common\model\Links::all();
        $this->view->assign('links',$links);
        $this->view->assign('page_title','友情链接');
       return  $this->view->fetch();
    }
}