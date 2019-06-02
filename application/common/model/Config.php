<?php
/**
 * Copyright (c) 2019.
 * Author:Alone88
 * Github:https://github.com/anhao
 */

namespace app\common\model;


use think\Model;

class Config extends Model
{

    protected $type = [
        'is_auto' => 'boolean',
        'is_quest_cache' => 'boolean'
    ];


}