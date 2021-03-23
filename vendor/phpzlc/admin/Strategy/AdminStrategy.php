<?php

/**
 * 后台策略
 */

namespace PHPZlc\Admin\Strategy;

use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminStrategy extends AbstractController
{
    /**
     * 路由锚点session名
     */
    const URL_ANCHOR_POINT = 'URL_ANCHOR_POINT';

    /**
     * 复杂模式
     */
    const menu_model_all = 1;

    /**
     * 简单模式
     */
    const menu_model_simple = 2;

    /**
     * @var string
     */
    private static $assetBaseUrl;

    /**
     * @var string 页面标记
     */
    private static $page_tag;

    /**
     * @var string 后台标题
     */
    private static $title;

    /**
     * @var string 后台favicon_ico图标
     */
    private static $favicon_ico;

    /**
     * @var string 后台logo
     */
    private static $logo;

    /**
     * @var string 后台入口url
     */
    private static $entrance_url;

    /**
     * @var string 后台出口url
     */
    private static $end_url = '#';

    /**
     * @var string 设置密码url
     */
    private static $setting_pwd_url = '#';

    /**
     * @var string 清除缓存API地址url
     */
    private static $clear_cache_api_url = '';

    /**
     * @var string 管理员名称
     */
    private static $admin_name = 'admin';

    /**
     * @var string
     */
    private static $admin_avatar;

    /**
     * @var static 管理员角色名称
     */
    private static $admin_role_name = '超级管理员';

    /**
     * @var Menu[]
     */
    private static $menus = [];

    /**
     * @var integer
     */
    private static $menu_model = self::menu_model_all;

    /**
     * @var string 登陆页面背景图片
     */
    private static $login_lack_ground_img;

    /**
     * @var Navigation[]
     */
    private static $navigations = [];

    /**
     * @var TopMenu[]
     */
    private static $topMenus = [];

    /**
     * @var string
     */
    private static $hend_code = '';


    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    public function setUrlAnchor()
    {
        $this->get('session')->set(self::URL_ANCHOR_POINT, $this->get('request_stack')->getCurrentRequest()->getUri());

        return $this;
    }

    public function getUrlAnchor()
    {
        return $this->get('session')->get(self::URL_ANCHOR_POINT);
    }

    public function setPageTag($tag)
    {
        static::$page_tag = $tag;

        return $this;
    }

    public function getPageTag()
    {
        return static::$page_tag;
    }

    public function setTitle($title)
    {
        static::$title = $title;

        return $this;
    }

    public function getTitle()
    {
        return static::$title;
    }

    public function setFaviconIco($favicon_ico)
    {
        static::$favicon_ico = $favicon_ico;

        return $this;
    }

    public function getFaviconIco()
    {
        return static::$favicon_ico;
    }

    public function setLogo($logo)
    {
        static::$logo = $logo;

        return $this;
    }

    public function getLogo()
    {
        if(empty(static::$logo)){
            if(empty($this->getAssetBaseUrl())){
                return 'bundles/phpzlcadmin/images/logo.png';
            }else{
                return $this->getAssetBaseUrl() . '/bundles/phpzlcadmin/images/logo.png';
            }
        }

        return static::$logo;
    }

    public function setEndUrl($end_url)
    {
        static::$end_url = $end_url;

        return $this;
    }

    public function getEndUrl()
    {
        return static::$end_url;
    }

    public function setEntranceUrl($entrance_url)
    {
        static::$entrance_url = $entrance_url;

        return $this;
    }

    public function getEntranceUrl()
    {
        return static::$entrance_url;
    }

    public function setSettingPwdUrl($setting_pwd_url)
    {
        static::$setting_pwd_url  = $setting_pwd_url;

        return $this;
    }

    public function getSettingPwdUrl()
    {
        return static::$setting_pwd_url ;
    }

    public function setAdminName($admin_name)
    {
        self::$admin_name = $admin_name;

        return $this;
    }

    public function getAdminName()
    {
        return self::$admin_name;
    }

    public function setAdminRoleName($admin_role_name)
    {
        self::$admin_role_name = $admin_role_name;

        return $this;
    }

    public function getAdminRoleName()
    {
        return self::$admin_role_name;
    }

    /**
     * @return Menu[]
     */
    public function getMenus(): array
    {
        return self::$menus;
    }

    /**
     * @param array $menus
     * @return $this
     */
    public function setMenus(array $menus)
    {
        self::$menus = $menus;

        return $this;
    }

    /**
     * @return int
     */
    public function getMenuModel(): int
    {
        return self::$menu_model;
    }

    /**
     * @param int $menu_model
     *
     * @return $this
     */
    public function setMenuModel(int $menu_model)
    {
        self::$menu_model = $menu_model;

        return $this;
    }

    public function getAdminEnv()
    {
        return $_ENV['ADMIN_ENV'];
    }

    /**
     * @return string
     */
    public function getLoginLackGroundImg(): string
    {
        if(empty(self::$login_lack_ground_img)){
            if(empty($this->getAssetBaseUrl())){
                return 'bundles/phpzlcadmin/images/login_logo.png';
            }else {
                return $this->getAssetBaseUrl() . '/bundles/phpzlcadmin/images/login_logo.png';
            }
        }

        return self::$login_lack_ground_img;
    }

    /**
     * @param string $login_lack_ground_img
     * @return $this
     */
    public function setLoginLackGroundImg(string $login_lack_ground_img)
    {
        self::$login_lack_ground_img = $login_lack_ground_img;

        return $this;
    }

    /**
     * @return Navigation[]
     */
    public function getNavigations(): array
    {
        return self::$navigations;
    }

    /**
     * @param array $navigations
     * @return $this
     */
    public function setNavigations(array $navigations)
    {
        self::$navigations = $navigations;

        return $this;
    }

    public function addNavigation(Navigation $navigation)
    {
        self::$navigations[] = $navigation;

        return $this;
    }

    /**
     * @return TopMenu[]
     */
    public static function getTopMenus(): array
    {
        return self::$topMenus;
    }

    /**
     * @param TopMenu[] $topMenus
     */
    public function setTopMenus(array $topMenus)
    {
        self::$topMenus = $topMenus;

        return $this;
    }

    public function addTopMenu(TopMenu $topMenu)
    {
        self::$topMenus[] = $topMenu;

        return $this;
    }


    public function setAssetBaseUrl($access_base_url)
    {
        self::$assetBaseUrl = $access_base_url;

        return $this;
    }

    public function getAssetBaseUrl()
    {
        return self::$assetBaseUrl;
    }

    public function getBaseUrl()
    {
        return str_replace('/index.php', '',  $this->container->get('request_stack')->getCurrentRequest()->getBaseUrl());
    }

    /**
     * @return string
     */
    public function getClearCacheApiUrl()
    {
        return self::$clear_cache_api_url;
    }

    public function setClearCacheApiUrl(string $clear_cache_api_url)
    {
        self::$clear_cache_api_url = $clear_cache_api_url;

        return $this;
    }

    public function getHendCode() : string
    {
        return self::$hend_code;
    }

    /**
     * @param $hend_code
     */
    public function setHendCode(string $hend_code)
    {
        self::$hend_code = $hend_code;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdminAvatar(): string
    {
        if(empty(self::$admin_avatar)){
            if(empty($this->getAssetBaseUrl())){
                return 'bundles/phpzlcadmin/images/admin_avatar.png';
            }else {
                return $this->getAssetBaseUrl() . '/bundles/phpzlcadmin/images/admin_avatar.png';
            }
        }

        return self::$admin_avatar;
    }

    /**
     * @param string $admin_avatar
     */
    public function setAdminAvatar(string $admin_avatar)
    {
        self::$admin_avatar = $admin_avatar;

        return $this;
    }
}