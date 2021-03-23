<?php
/**
 * 当前授权登录用户信息类
 * 
 * Created by Trick
 * user: Trick
 * Date: 2020/12/21
 * Time: 10:21 上午
 */

namespace App\Business\AuthBusiness;

use App\Entity\UserAuth;
use App\Business\AuthBusiness\UserInterface;

class CurAuthSubject
{
    /**
     * 当前管理员授权信息
     *
     * @var UserAuth
     */
    private static $cur_user_auth;

    /**
     * 当前管理员信息
     *
     * @var UserInterface
     */
    private static $cur_user;

    /**
     * 当前可跳转路由
     *
     * @var string
     */
    private static $cur_auth_success_go_url = '';

    /**
     * 设置当前管理员授权信息
     * 
     * @param UserAuth $userAuth
     */
    public static function setCurUserAuth(UserAuth $userAuth)
    {
        self::$cur_user_auth = $userAuth;
    }

    /**
     * 设置当前可跳转路由
     *
     * @param $cur_auth_success_go_url
     */
    public static function setCurAuthSuccessGoUrl($cur_auth_success_go_url)
    {
        self::$cur_auth_success_go_url = $cur_auth_success_go_url;
    }

    /**
     * 获取当前管理员授权信息
     * 
     * @return mixed
     */
    public static function getCurUserAuth()
    {
        return self::$cur_user_auth;
    }

    /**
     * 获取当前可跳转路由
     * 
     * @return string
     */
    public static function getCurAuthSuccessGoUrl()
    {
        return self::$cur_auth_success_go_url;
    }

    /**
     * 设置当前登录用户信息
     *
     * @param UserInterface $user
     */
    public static function setCurUser(UserInterface $user)
    {
        self::$cur_user = $user;
    }

    /**
     * 获取当前登录管理员信息
     *
     * @return UserInterface
     */
    public static function getCurUser()
    {
        return self::$cur_user;
    }
}