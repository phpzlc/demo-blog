<?php
/**
 * 平台标示
 *
 * user: Trick
 * Date: 2020/12/18
 * Time: 10:49 上午
 */

namespace App\Business\PlatformBusiness;

use PHPZlc\PHPZlc\Bundle\Business\AbstractBusiness;
use Psr\Container\ContainerInterface;

class PlatformClass extends AbstractBusiness
{
    const NOT_LOGIN_GO_URL = 'not_login_go_url';
    
    /**
     * 平台名称
     * 
     * @var string
     */
    private static $platform;

    /**
     * 获取平台名称
     * 
     * @return string
     */
    public static function getPlatform()
    {
        return self::$platform;
    }

    /**
     * 设置平台名称
     * 
     * @param $platform
     */
    public static function setPlatform($platform)
    {
        self::$platform = $platform;
    }

    /**
     * 得到所有平台名称
     *
     * @param ContainerInterface $container
     * @return array
     */
    public static function getPlatforms(ContainerInterface $container)
    {
        return array();
    }
}
