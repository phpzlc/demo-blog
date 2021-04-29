<?php
/**
 * 博客基础类
 *
 * Created by Trick
 * user: Trick
 * Date: 2021/4/15
 * Time: 2:39 下午
 */

namespace App\Controller\Blog;

use App\Business\AuthBusiness\UserAuthBusiness;
use App\Business\PlatformBusiness\PlatformClass;
use App\Entity\UserAuth;
use PHPZlc\Admin\Strategy\AdminStrategy;
use PHPZlc\Admin\Strategy\Menu;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use PHPZlc\PHPZlc\Responses\Responses;

class BlogBaseController extends SystemBaseController
{
    /**
     * 当前登录管理员授权信息
     *
     * @var UserAuth
     */
    protected $curUserAuth;

    /**
     * 管理策略
     *
     * @var AdminStrategy
     */
    protected $adminStrategy;

    /**
     * 页面标记
     *
     * @var string
     */
    protected $page_tag;

    public function inlet($returnType = SystemBaseController::RETURN_HIDE_RESOURCE, $isLogin = true)
    {
        PlatformClass::setPlatform('blog');

        $menus = [
            new Menu('首页', null, 'blog_index', $this->generateUrl('blog_index'), null),
            new Menu('分类', null, 'blog_types', $this->generateUrl('blog_types'), null),
            new Menu('标签', null, 'blog_tags', $this->generateUrl('blog_tags'), null),
            new Menu('关于我', null, 'blog_about', $this->generateUrl('blog_about'), null)
        ];

        $this->adminStrategy = new AdminStrategy($this->container);

        $this->adminStrategy
            ->setPageTag($this->page_tag)
            ->setMenus($menus);

        $r = parent::inlet($returnType, $isLogin);
        if($r !== true){
            return $r;
        }

        if($isLogin){
            $userAuthBusiness = new UserAuthBusiness($this->container);
            $userAuth = $userAuthBusiness->isLogin();

            $this->curUserAuth = $userAuth;


            if($userAuth === false){
                if(self::getReturnType() === SystemBaseController::RETURN_HIDE_RESOURCE){
                    return Responses::error(Errors::getError()->msg, -1, ['go_url' => $this->generateUrl('blog_login_page')]);
                }else{
                    return $this->redirect($this->generateUrl('blog_login_page'));
                }
            }
        }

        return true;
    }
}