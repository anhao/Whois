<?php
/**
 * Copyright (c) 2019.
 * Author:Alone88
 * Github:https://github.com/anhao
 */
namespace app\facade;

use think\Facade;

class Whois extends Facade{

    protected static function getFacadeClass()
    {
        return 'app\common\Whois';
    }
}