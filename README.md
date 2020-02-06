# Whois 查询系统
基于Thinkphp+Mysql 的域名Whois 信息查询系统
理论上支持所有后缀,支持中文IDN后缀

# 更新
2020.02.06 更新，程序缺少了文件，导致部署不成功！现在已更新！


# 使用
 - 把`public` 设置为应用目录
 - 上传数据库,config/database.php 配置数据库
 - 后台配置网站信息 `域名/admin`
 - 后台可以设置Whois查询服务器,默认有了1000条左右
 - 可以缓存查询的Whois信息
 - 默认账号`admin` 密码`admin`
## 伪静态

`apache`
```apacheconfig
<IfModule mod_rewrite.c>
  Options +FollowSymlinks -Multiviews
  RewriteEngine On

  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]
</IfModule>
```

`nginx`
```nginx
location / {
	if (!-e $request_filename){
		rewrite  ^(.*)$  /index.php?s=$1  last;   break;
	}
}
```