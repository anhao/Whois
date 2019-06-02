<?php
/**
 * Copyright (c) 2019.
 * Author:Alone88
 * Github:https://github.com/anhao
 */

namespace app\common\model;


use think\Model;

class Info extends Model
{

    protected $dateFormat = 'Y年m月d日 H:i:s';
    protected $autoWriteTimestamp = true;
    protected $updateTime = 'update_time';

   /* public function getInfoAttr($value){
        return substr($value,0,50)."...";
    }*/

}