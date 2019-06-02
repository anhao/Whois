<?php
/**
 * Copyright (c) 2019.
 * Author:Alone88
 * Github:https://github.com/anhao
 */

namespace app\index\controller;


use app\common\controller\Base;

class About extends Base
{
    public function index(){
        $this->view->assign('page_title','关于我们');
        return $this->view->fetch();
    }

}