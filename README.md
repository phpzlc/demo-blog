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

//安装资源
php bin/console assets:install  

//内置数据(APP_ENV=dev环境下可执行)
php bin/console doctrine:fixtures:load  --append

//生成文档(非必须)
php bin/console phpzlc:generate:document
```

## 访问

推荐浏览器: **谷歌浏览器**

浏览器访问项目`public`目录。

博客地址: {project_public_dir}/index.php/blog/

后台地址: {project_public_dir}/index.php/admin/

接口文档地址: {project_public_dir}/apidoc/index.html

后台管理员账号密码: aitime 123456

## 功能介绍

### 前台功能

1. 登录

    ![登录](/public/readme/fore-end/login.png)

2. 博客列表
    
    ![列表](/public/readme/fore-end/index.png)

3. 博客详情.
    
    ![详情](/public/readme/fore-end/article.png)
    
4. 博客分类

    ![分类](/public/readme/fore-end/classify.png)
    
5. 博客标签
    
    ![标签](/public/readme/fore-end/label.png)
    
6. 关于我

    ![关于我](/public/readme/fore-end/about.png)   

### 后台功能

1. 登录，修改密码，退出登录。

    ![登录](/public/readme/index.png)
    
2. 博客分类管理（一级)

    ![分类](/public/readme/classify.png)
    
3. 博客管理 

     发布编辑删除
     
      ![博客](/public/readme/editArticle.png)
      
     评论管理
     
     ![评论](/public/readme/comment.png)
     
     发布编辑 （博客标签）
     
     ![博客](/public/readme/editLable.png)
     
    
4. 用户管理

     用户名，最后登录时间， 禁用启用
     
     ![用户](/public/readme/user.png)
     
5. 收藏管理
    
    ![收藏](/public/readme/collection.png)     
     
6. 控制台

    总用户数
    
    总博客数
    
    总评论数
    
    总收藏数
    
    ![控制台](/public/readme/console.png)
    
8. 报表

    主分类下的博客数，收藏数
    
    ![控制台](/public/readme/console.png)
    
9. 权限系统

    账号与角色管理
    
    ![角色](/public/readme/adminRole.png)
    
    ![角色](/public/readme/editAdminRole.png)
    
    角色与权限管理
    
    ![权限](/public/readme/roleIndex.png)
    
    ![权限](/public/readme/editRole.png)
    
    
    