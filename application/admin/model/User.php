<?php
/**
 * Copyright (c) 2019.
 * Author:Alone88
 * Github:https://github.com/anhao
 */

namespace app\admin\model;


use think\Model;

class User extends Model
{
    protected $pk = 'uid';

    public function setPassAttr($value){
        return sha1($value);
    }
    public function getNameAttr($value){
        return strtolower($value);
    }
    public function setNameAttr($value){
        return strtolower($value);
    }

}