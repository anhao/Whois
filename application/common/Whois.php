<?php
/**
 * Copyright (c) 2019.
 * Author:Alone88
 * Github:https://github.com/anhao
 */

namespace app\common;

use app\common\model\Config;

/**
 * Class Whois
 *
 * error
 * 100 请求成功
 * 101 域名格式错误
 * 102 请求错误
 * 103 不支持的后缀
 * @package app\common
 */
class Whois
{

    private $domain; //域名
    private $tld; //后缀
    private $sub;//前缀
    private $prot = 43; //whois 查询端口
    private $timeout = 20; // 查询超时秒
    private $retry = 0;
    private $is_auto = false; // 是否自动适配Whois服务器,默认关闭

    public function __construct()
    {

    }

    public function query($domain)
    {
        // 转换中文IDN
        $domain = idn_to_ascii($domain,IDNA_NONTRANSITIONAL_TO_ASCII,INTL_IDNA_VARIANT_UTS46);
        $is_auto = Config::get(1);
        $this->is_auto = $is_auto->is_auto;

        $matches = $this->domianCheck($domain);

        if (false === $matches) {
            return [
                'errno' => 101,
                'info' => '域名格式错误'
            ];
        }

        $this->sub = strtolower($matches[0]);
        $this->tld = strtolower($matches[1]);
        $this->domain = $this->sub . '.' . $this->tld;
        $server = $this->server($this->tld, $this->is_auto);

        if (!$server) {
            return array(
                'errno' => 103,
                'info' => "{$this->domain} 不支持的域名后缀");
        } else {
            $result = $this->socket($server);
        }

        /*if (preg_match('/^([-a-z0-9]{1,100})\.([a-z\.]{2,8})$/i', $domain, $matches)) {
            $this->sub = strtolower($matches[1]);
            $this->tld = strtolower($matches[2]);
            $this->domain = $this->sub . '.' . $this->tld;
            $server = $this->server($this->tld);

            if (!$server) {
                return array(
                    'errno' => 103,
                    'info' => "{$this->domain} 不支持的域名后缀");
            } else {
                $result = $this->socket($server);
            }
        } else {
            return (
            [
                'errno' => 101,
                'info' => '域名格式错误'
            ]
            );
        }*/
        if ($result) {
            return [
                'errno' => 100,
                'info' => $result
            ];
        } else {
            return [
                'errno' => 102,
                'info' => '查询失败'
            ];
        }
    }

    private function domianCheck($domain)
    {
        $pattern1 = '/^([-a-z0-9]{1,255})\.([-a-z0-9\.]{2,50})$/i';
        $pattern2 = '/^(www)\.([-a-z0-9]{1,255})\.([-a-z0-9\.]{2,50})$/i';
        if (preg_match($pattern1, $domain, $matches)) {
            $matches = [$matches[1], $matches[2]];
            return $matches;
        } else if (preg_match($pattern2, $domain, $matches)) {
            $matches = [$matches[2], $matches[3]];
            return $matches;
        } else {
            return false;
        }
    }

    /** 通过tld 查询whois 服务器
     * @param $tld  域名后缀
     * @param bool $default 是否开启适配whois服务器
     * @return bool|string //返回whois 服务器
     */
    private function server($tld, $is_auto)
    {
        $server = \app\common\model\Server::getServer();
        if (array_key_exists($tld, $server)) {
            return $server[$tld][mt_rand(0, count($server[$tld]) - 1)];
        } else {
            if ($is_auto) {
                return 'whois.nic.' . $tld;
            } else {
                return false;
            }
        }
    }

    private function socket($nic, $retry = 0)
    {
        // 建立通信
        try {
            $socket = fsockopen($nic, $this->prot, $errno, $errstr, $this->timeout);
            // 验证通信
            if ($socket == false) {
                //是否重试
                if ($retry < $this->retry) {
                    return $this->socket($nic, $retry + 1);
                } else {
                    return false;
                }
            }

            fputs($socket, $this->domain . "\r\n");

            //取出相应
            $response = '';
            while (!feof($socket)) {
                $response .= fgets($socket);
            }
            //关闭通信
            fclose($socket);

            //转换编码
            $encoding = array('UTF-8', 'ISO-8859-1', 'ISO-8859-15', 'ASCII', 'CP936', 'EUC-CN', 'BIG-5', 'JIS', 'eucJP-win', 'SJIS-win', 'EUC-JP');
            $response = htmlspecialchars(mb_convert_encoding($response, 'UTF-8', mb_detect_encoding($response, $encoding, true)));

            return $response;
        } catch (\Exception $e) {
            return false;
        }
    }
}