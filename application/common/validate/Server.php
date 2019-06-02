<?php
/**
 * Copyright (c) 2019.
 * Author:Alone88
 * Github:https://github.com/anhao
 */

namespace app\common\validate;


use think\Validate;

class Server extends Validate
{
    protected $rule =[
        'tld|域名后缀' => 'require|min:2',
        'server|Whois服务器' => 'require|min:3',
        'state|状态' => 'require|max:1'
    ];
}