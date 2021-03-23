<?php
/**
 * AuthTag类
 * 
 * Created by Trick
 * user: Trick
 * Date: 2020/12/21
 * Time: 3:30 下午
 */

namespace App\Business\AuthBusiness;


use App\Business\PlatformBusiness\PlatformClass;
use App\Entity\UserAuth;
use Doctrine\Persistence\ManagerRegistry;
use PHPZlc\PHPZlc\Abnormal\Error;
use PHPZlc\PHPZlc\Abnormal\Errors;
use Psr\Container\ContainerInterface;
use Exception;

class AuthTag
{
    /**
     * 设置Session标记
     * 
     * @param ContainerInterface $container
     * @param UserAuth $userAuth
     * @return string
     * @throws Exception
     */
    public static function set(ContainerInterface $container, UserAuth $userAuth)
    {
        $tag = '';

        switch (PlatformClass::getPlatform()){
            case $container->get('parameter_bag')->get('platform_admin'):
                $container->get('session')->set(PlatformClass::getPlatform() . $container->get('parameter_bag')->get('login_tag_session_name'), $userAuth->getId());
                break;

            default:
                throw new \Exception('来源溢出');
        }

        return $tag;
    }

    /**
     * 获取Session标记内容
     * 
     * @param ContainerInterface $container
     * @return UserAuth|false|object
     * @throws Exception
     */
    public static function get(ContainerInterface $container)
    {
        $userAuth = null;
        /**
         * @var ManagerRegistry $doctrine
         */
        $doctrine = $container->get('doctrine');

        switch (PlatformClass::getPlatform()){
            case $container->get('parameter_bag')->get('platform_admin'):
                $user_auth_id = $container->get('session')->get(PlatformClass::getPlatform() . $container->get('parameter_bag')->get('login_tag_session_name'));
                $userAuth = $doctrine->getRepository('App:UserAuth')->find($user_auth_id);
                if(empty($userAuth)){
                    Errors::setError(new Error('登录失效，请重新登录', -1));
                    return false;
                }
                break;

            default:
                throw new \Exception('来源溢出');
        }

        return $userAuth;
    }

    /**
     * 移除Session标记
     * 
     * @param ContainerInterface $container
     * @throws Exception
     */
    public static function remove(ContainerInterface $container)
    {
        switch (PlatformClass::getPlatform()){
            case $container->get('parameter_bag')->get('platform_admin'):
                $container->get('session')->remove(PlatformClass::getPlatform() . $container->get('parameter_bag')->get('login_tag_session_name'));
                break;

            default:
                throw new \Exception('来源溢出');
        }
    }

}