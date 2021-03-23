<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/7/28
 */

namespace PHPZlc\Admin\Bundle\Extension;


use PHPZlc\Admin\Strategy\AdminStrategy;
use PHPZlc\Validate\Validate;
use Psr\Container\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class StrategyExtension extends AbstractExtension
{
    private $adminStrategy;

    public function __construct(ContainerInterface $container = null)
    {
        $this->adminStrategy = new AdminStrategy($container);
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('admin_title', [$this->adminStrategy, 'getTitle']),
            new TwigFunction('admin_favicon_ico', [$this->adminStrategy, 'getFaviconIco']),
            new TwigFunction('admin_entrance_url', [$this->adminStrategy, 'getEntranceUrl']),
            new TwigFunction('admin_logo', [$this->adminStrategy, 'getLogo']),
            new TwigFunction('admin_end_url', [$this->adminStrategy, 'getEndUrl']),
            new TwigFunction('admin_page_tag', [$this->adminStrategy, 'getPageTag']),
            new TwigFunction('admin_setting_pwd_url', [$this->adminStrategy, 'getSettingPwdUrl']),
            new TwigFunction('admin_name', [$this->adminStrategy, 'getAdminName']),
            new TwigFunction('admin_role_name', [$this->adminStrategy, 'getAdminRoleName']),
            new TwigFunction('admin_avatar', [$this->adminStrategy, 'getAdminAvatar']),
            new TwigFunction('admin_url_anchor', [$this->adminStrategy, 'getUrlAnchor']),
            new TwigFunction('admin_env', [$this->adminStrategy, 'getAdminEnv']),
            new TwigFunction('admin_menus', [$this->adminStrategy, 'getMenus']),
            new TwigFunction('admin_top_menus', [$this->adminStrategy, 'getTopMenus']),
            new TwigFunction('admin_menu_model', [$this->adminStrategy, 'getMenuModel']),
            new TwigFunction('admin_login_lack_ground_img', [$this->adminStrategy, 'getLoginLackGroundImg']),
            new TwigFunction('admin_navigations', [$this->adminStrategy, 'getNavigations']),
            new TwigFunction('admin_asset', [$this, 'asset']),
            new TwigFunction('admin_clear_cache_api_url', [$this->adminStrategy, 'getClearCacheApiUrl']),
            new TwigFunction('admin_hend_code', [$this->adminStrategy, 'getHendCode']),
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('explode', [$this, 'explode']),
            new TwigFilter('boolToString', [$this, 'boolToString']),
            new TwigFilter('boolToInt', [$this, 'boolToInt'])
        ];
    }

    public function boolToString($bool, $strings = ['是', '否'])
    {
        return $bool ? $strings[0] : $strings[1];
    }

    public function boolToInt($bool)
    {
        return $bool ? 1: 0;
    }

    public function explode($string, $delimiter = ',')
    {
        return explode($delimiter, $string);
    }

    public function asset($path)
    {
        if(Validate::isUrl($path)){
            return $path;
        }

        if(!empty($this->adminStrategy->getAssetBaseUrl())){
            return $this->adminStrategy->getAssetBaseUrl() . '/' . $path;
        }else{
            return $this->adminStrategy->getBaseUrl() . '/' . $path;
        }
    }
}