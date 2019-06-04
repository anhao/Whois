<?php

namespace app\index\controller;

use app\common\controller\Base;
use app\common\model\Info;
use app\facade\Whois;
use think\captcha\Captcha;
use think\Request;


// 输入域名 -》 数据库是否有数据
//有数据,返回数据库数据,页面显示是否更新缓存
//没有数据,查询whois,查询到了存入数据库,返回数据库信息
//

/**
 * Class Index
 * @package app\index\controller
 */
class Index extends Base
{

    /**
     * @return string
     * @throws \Exception
     */
    public function index()
    {

        $this->view->assign('page_title', '首页');
        return $this->view->fetch();
    }

    /**
     * @param Request $request
     * @param boolean $up_cache 是否更新缓存
     * @param boolean $is_quest_cache 是否开启请求缓存
     * @param int $quest_cache_time 缓存时间,单位 秒
     * @return mixed|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function query(Request $request)
    {

        $domain = urldecode($request->param('domain'));
        $is_quest_cache = $this->conifg['is_quest_cache'];
        $quest_cache_time = $this->conifg['quest_time'];

        if (!$domain) {
            return '域名为空';
        }

        if (true == $is_quest_cache) {
            $info = $this->isQuestCache($domain, $quest_cache_time);
        } else {
            $info = $this->noQuestCache($domain);
        }

        $this->view->assign('page_title',$domain);
        $this->view->assign('info', $info);

        return $this->fetch();
    }

    /** 请求缓存
     * @param $domain
     * @param int $quest_cache_time
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function isQuestCache($domain, $quest_cache_time = 60)
    {

        $info = new Info();
        $info::where('domain', $domain)->cache($domain, $quest_cache_time)->find();

        $has_domain = cache($domain);
        // 判断数据库是否有数据
        if ($has_domain) {
            return [
                'domain' => $has_domain['domain'],
                'info' => $has_domain['info'],
                'update_time' => date('Y年m月d日 H:i:s', $has_domain['update_time'])
            ];
        } else {
            //  查询whois，成功则插入数据库
            $result = Whois::query($domain);
            if ($result['errno'] == 100) {
                $info->domain = $domain;
                $info->info = $result['info'];
                $info->replace()->save();
            }
            return [
                'domain' => $domain,
                'info' => $result['info']
            ];
        }
    }

    /** 请求不缓存
     * @param $domain
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function noQuestCache($domain)
    {
        $info = new Info();
        $has_domain = $info::where('domain', $domain)->find();

        // 判断数据库是否有数据
        if ($has_domain) {
            // 如果有数据直接返回
            return $has_domain;
        } else {
            //  查询whois
            $result = Whois::query($domain);
            if ($result['errno'] == 100) {
                $info->domain = $domain;
                $info->info = $result['info'];
                $info->update_time = time();
                $info->replace()->save();
                return $info;
            } else {
                //查询失败
                return [
                    'domain' => $domain,
                    'info' => $result['info']
                ];
            }
        }

    }

    /**
     *  生成验证码
     */
    public function verify()
    {
        $config = [
            'fontSize' => 18,
            'length' => 4,
            'imageH' => 36,
            'imageW' => 132,
        ];
        $captcha = new Captcha($config);
        return $captcha->entry();
    }

    /** 更新缓存
     * @param $domain
     * @return mixed
     */
    public function refreshCache(Request $request)
    {

        $domain = $request->post('domain');

        $captcha = $request->post('captcha');

        if (!captcha_check($captcha)) {
            $this->error('验证码错误');
        }

        $result = Whois::query($domain);

        if($result['errno'] == 100){
            //更新数据并更新缓存
            $res = Info::cache($domain)->where('domain', $domain)->update(['info' => $result['info'], 'update_time' => time()]);
            if ($res) {
                $this->success('更新成功', '/' . $domain);
            } else {
                $this->error('更新失败');
            }
        }else{
            $this->error('更新失败');
        }
    }

}
