<?php
/**
 * Copyright (c) 2019.
 * Author:Alone88
 * Github:https://github.com/anhao
 */

namespace app\admin\controller;


use app\admin\model\User;
use app\common\controller\Base;
use app\common\model\Config;
use app\common\model\Info;
use app\common\model\Links;
use app\common\model\Server;
use think\Exception;
use think\facade\Cache;
use think\facade\Session;
use think\Request;

class Index extends Base
{

    /** 后台首页
     * @return string
     * @throws \Exception
     */
    public function index()
    {
        $this->isLogin();
        $this->view->assign('pageName', '后台');

        //获取统计信息
        $serverCount = Server::count('id');
        $infoCount = Info::count('id');
        $info = User::get(Session::get('user_id'));

        //传递视图
        $this->view->assign('serverCount', $serverCount);
        $this->view->assign('infoCount', $infoCount);
        $this->view->assign('info', $info);

        return $this->view->fetch();
    }

    /** 登录页面
     * @return string
     * @throws \Exception
     */
    public function Login()
    {
        $this->logined();
        $this->view->assign('pageName', '后台登录');
        return $this->view->fetch();
    }

    /** 验证登录信息
     * @param Request $request
     */
    public function checkLogin(Request $request)
    {
        if (!$request->isPost()) {
            $this->error('请求错误');
        } else {
            $data = $request->only(['name', 'pass']);
            $res = User::get(function ($result) use ($data) {
                $result->where('name', $data['name'])
                    ->where('pass', sha1($data['pass']));
            });
            if (null != $res) {
                Session::set('user_id', $res->uid);
                Session::set('user_name', $res->name);
                Session::set('user_email', $res->email);
                $this->success('登录成功', '/admin');
            } else {
                $this->error('账号或密码错误');
            }
        }
    }

    /**
     *  退出登录
     */
    public function logout()
    {
        Session::clear();
        $this->success('退出登录成功', 'index');
    }

    /** 网站信息配置
     * @return string
     * @throws \Exception
     */
    public function site()
    {
        $this->isLogin();
        return $this->view->fetch();
    }

    /** 验证网站信息&更新
     * @param Request $request
     */
    public function checkSiteUpdate(Request $request)
    {
        $this->isLogin();
        $data = $request->post();
        $config = Config::get(1);
        if ($config->save($data)) {
            //清除缓存更新
            if(cache::rm('config')){
                $this->success('更新成功');
            }
        } else {
            $this->error('更新失败');
        }
    }

    /** 用户更新 页面
     * @return string
     * @throws \Exception
     */
    public function update()
    {
        $this->isLogin();
        $info = User::get(Session::get('user_id'));
        $this->view->assign('info', $info);
        return $this->view->fetch();
    }

    /** 验证 用户更新
     * @param Request $request
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function checkupdate(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->post();
            $rule = [
                'email|邮箱' => 'require|email',
                'name|用户名' => 'require|min:5|alphaNum',
                'pass|密码' => 'min:5',
                'pass_confirm|二次密码' => 'confirm:pass'
            ];
            $res = $this->validate($data, $rule);
            if (true !== $res) {//验证失败
                $this->error($res);
            } else {
                if (!empty($data['pass'])) {//判断是否输入密码,输入则更新密码
                    $update = User::where('uid', Session::get('user_id'))->update([
                        'email' => $data['email'],
                        'name' => $data['name'],
                        'pass' => sha1($data['pass'])
                    ]);
                } else {
                    $update = User::where('uid', Session::get('user_id'))->update([
                        'email' => $data['email'],
                        'name' => $data['name'],
                    ]);
                }
                if ($update) {
                    return $this->success('更新成功');
                } else {
                    return $this->error('更新失败');
                }
            }
        } else {
            $this->error('请求类型出错');
        }
    }

    /** Whois 信息
     * @return mixed
     */
    public function whoisServer()
    {
        $this->isLogin();
        return $this->fetch();
    }

    /** 返回 Whois 服务器信息分页
     * @return array
     */
    public function whoisJson()
    {
        $this->isLogin();
        $json = [
            'data' => Server::select(function ($query) {
                $query->order('id', 'desc');
            })
        ];
        $ajax = [
            'draw' => 1,
            'recordsTotal' => count($json),
            'recordsFiltered' => count($json),
            'data' => $json
        ];
        return $json;
    }

    /**
     * 添加单条Whois服务器
     */
    public function whoisAdd()
    {
        $this->isLogin();
        $this->fetch();
    }


    /**
     * 批量添加Whois,如果没按格式来回报错
     *  格式：com,whois.nic.com|xyz,whois.nic.xyz
     * @param Request $request
     * @throws \Exception
     */
    public function whoisLotAdd(Request $request)
    {

        $this->isLogin();
        try {
            $str = $request->param('whois');

            $arr1 = explode('|', $str);

            for ($i = 0; $i < count($arr1); $i++) {
                $arr2[] = explode(',', $arr1[$i]);
            }
            for ($j = 0; $j < count($arr2); $j++) {
                $list[$j] = ['tld' => $arr2[$j][0], 'server' => $arr2[$j][1]];
            }
            $server = new Server();
            if ($server->saveAll($list)) {
                $this->success('添加成功');
            } else {
                $this->error('更新失败');
            }
        }catch (Exception $e){
            $this->error('数据错误');
        }
    }

    /** 验证添加的Whois 服务器信息
     * @param Request $request
     */
    public function whoisAddCheck(Request $request)
    {
        $this->isLogin();
        $data = $request->post();
        $rule = [
            'tld|域名后缀' => 'require|min:2',
            'server|Whois服务器' => 'require|min:3',
            'state|状态' => 'require|max:1'
        ];
        $res = $this->validate($data, $rule);

        if (true !== $res) { //验证失败
            $this->error($res);
        } else {
            $result = Server::create($data);
            if ($result) {
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
        }
    }

    /** 删除选中的Whois服务器
     * @param Request $request
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function whoisDelete(Request $request)
    {
        $id = $request->post('id');
        $res = Server::where('id', 'in', $id)->delete();
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /** 更新Whois  服务器
     * @param Request $request
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function whoisUpdate(Request $request)
    {
        $data = $request->post();
        $validata = $this->validate($data, 'app\common\validate\Server');
        if (true !== $validata) {
            $this->error($validata);
        }
        $res = Server::where('id', $data['id'])
            ->update(['tld' => $data['tld'], 'server' => $data['server'], 'state' => $data['state'], 'update_time' => time()]);
        if ($res == 1) {
            $this->success('更新成功');
        } else {
            $this->error('更新失败');
        }
    }

    /** 生成域名记录的分页
     * @return string
     * @throws \think\exception\DbException
     */
    public function domainInfo()
    {
        $this->isLogin();
        $infoList = Info::order('update_time', 'desc')->withAttr('Info', function ($value) {
            return substr($value, 0, 50) . "...";
        })->paginate(10, false, [
            'type' => '\app\admin\extend\Page'
        ]);
        $this->view->assign('infoList', $infoList);
        return $this->view->fetch();
    }

    /** 返回域名详细的 Json
     * @return mixed
     */
    public function domainJson()
    {
        $this->isLogin();
        $info = Info::select();
        return $info;
    }

    /**
     * 查看单条域名记录的详细详细
     * @param Request $request
     * @return array
     */
    public function domainFind(Request $request)
    {
        $this->isLogin();
        $id = $request->param('id');

        $res = Info::get($id);

        if (null != $res) {
            return [
                'status' => 1,
                'data' => $res
            ];
        } else {
            return [
                'status' => 0,
                'data' => '获取失败'
            ];
        }
    }

    /** 删除所选择的域名记录
     * @param Request $request
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function domainDelete(Request $request)
    {
        $this->isLogin();
        $id = $request->post('id');
        $res = Info::where('id', 'in', $id)->delete();
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     *  清空所有查询的域名记录
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function domainClear()
    {
        $this->isLogin();
        $res = Info::where('id', '>', '1')->delete();
        if ($res) {
            $this->success('清空成功');
        } else {
            $this->error('清空失败');
        }
    }

    /**
     * 清除缓存
     */
    public function clearCache()
    {
        if (\think\facade\Cache::clear()) {
            $this->success('清除缓存成功', 'admin/index/index');
        } else {
            $this->error('清除缓存失败', 'admin/index/index');
        }
    }



    // 其他功能 友情链接
    public function links(){
        $this->isLogin();
        $links = Links::order('id','desc')->paginate(10, false, [
            'type' => '\app\admin\extend\Page'
        ]);;
        $this->view->assign('links',$links);
       return  $this->view->fetch();
    }

    /**添加链接
     * @param Request $request
     */
    public function linksAdd(Request $request){
        $this->isLogin();
        $data = $request->post();
        $rule = [
          'name|名称'=>'require',
          'link|链接'=>'require'
        ];

        $validate = $this->validate($data,$rule);
        if(true != $validate){
            $this->error($validate);
        }
        $into = Links::create($data);
        if($into){
            $this->success('添加成功');
        }else{
            $this->success('添加失败');
        }
    }

    /** 更新
     * @param Request $request
     */
    public function linkUpdate(Request $request){
        $data = $request->post();
        $rule = [
            'name|名称'=>'require',
            'link|链接'=>'require'
        ];

        $validate = $this->validate($data,$rule);
        if(true != $validate){
            $this->error($validate);
        }
        $up = new Links();

        $res = $up->isUpdate(true)->save($data);

        if($res){
            $this->success("更新成功");
        }else{
            $this->error('更新失败');
        }
    }

    /**
     * 删除
     */
    public function linkDel(Request $request){
       $id =  $request->post();

       $res = Links::where('id','in',$id)->delete();

       if($res){
           $this->success('删除成功');
       }else{
           $this->error('删除失败');
       }
    }

}