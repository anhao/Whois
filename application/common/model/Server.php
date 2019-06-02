<?php
/**
 * Copyright (c) 2019.
 * Author:Alone88
 * Github:https://github.com/anhao
 */

namespace app\common\model;

use think\Model;

class Server extends Model
{

    protected $pk = 'id';
    protected $dateFormat = 'Y年m月d日 H:m:s';
    protected $type =[
        'create_time'   =>  'timestamp',
        'update_time'   =>  'timestamp',
    ];
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    public static function getServer()
    {
        $server = self::where('state', 1)->select()->toArray();

        $getServer = [];
        for ($i = 0; $i < count($server); $i++) {
            $getServer[$server[$i]['tld']][$i] = $server[$i]['server'];
        }
        foreach ($getServer as $k => $v) {
            $getServer[$k] = array_values($v);
        }
        return $getServer;
    }

    public function getStateAttr($value)
    {
        if ($value == 1) {
            return '启用';
        } else {
            return '禁用';
        }
    }

    public function setTldAttr($value){
        return strtolower($value);
    }
    public function setServerAttr($value){
        return strtolower($value);
    }
}