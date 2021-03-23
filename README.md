# Demo(示例项目-个人博客系统)

用于展示PHPZlc框架在实践场景下的使用方法。

## 环境要求

    php 7.3
    mysql 5.7 以上 可支持8.0版本以上
    
> php.ini   

```ini
upload_max_filesize = 1024M
post_max_size = 1024M
date.timezone = "Asia/Shanghai"
```

> nginx

```apacheconfig
client_max_body_size     1024M;
proxy_connect_timeout    9000s;
proxy_read_timeout       9000s;
proxy_send_timeout       9000s;
```

> mysql

```mysql.cnf
MySql 关闭 ONLY_FULL_GROUP_BY 参照链接 https://www.cnblogs.com/shoose/p/13259186.html
mysql5.7 及以上
[mysqld]
sql_mode ='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'
mysql8.0 及以上
[mysqld]
sql_mode ='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'
```

## 部署

```shell script
//项目配置
touch .env.local
vim .env.local
APP_ENV=prod  #生产环境配置
DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7  # 数据库配置

//文件夹权限
mkdir var
sudo chmod -R 777 var/ public/upload/

//创建数据库
php bin/console doctrine:database:create
php bin/console doctrine:schema:create

//内置数据
php bin/console doctrine:fixtures:load  --append

//安装资源
php bin/console assets:install  

//生成文档(非必须)
php bin/console phpzlc:generate:document
```

## 功能介绍

### 前台功能

1. 用户登录，个人中心，修改密码，登录，退出登录。

2. 收藏，取消收藏博客，博客列表。

3. 博客列表，博客详情。

### 后台功能

1. 登录，修改密码，退出登录。

2. 博客分类管理（两级）

3. 博客管理 

     发布编辑删除 
      
     评论管理
     
     发布编辑 （博客标签）
     
     收藏数
     
4. 用户管理

     用户名，收藏文章数， 最后登录时间， 禁用启用
     
5. 控制台

    总用户数
    
    总博客数
    
    总评论数
    
    总收藏数
    
7. 报表

    主分类下的博客数，收藏数

