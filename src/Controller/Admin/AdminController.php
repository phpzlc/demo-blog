<?php
/**
 * 管理平台基础类
 *
 * Created by Trick
 * user: Trick
 * Date: 2020/12/25
 * Time: 2:48 下午
 */

namespace App\Controller\Admin;

use App\Business\AuthBusiness\CurAuthSubject;
use App\Business\AuthBusiness\UserAuthBusiness;
use App\Business\PlatformBusiness\PlatformClass;
use App\Business\RBACBusiness\PermissionBusiness;
use App\Business\RBACBusiness\RBACBusiness;
use App\Entity\UserAuth;
use App\Repository\AdminRepository;
use PHPZlc\Admin\Strategy\AdminStrategy;
use PHPZlc\Admin\Strategy\Menu;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use PHPZlc\PHPZlc\Responses\Responses;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends SystemBaseController
{
    /**
     * 管理策略
     *
     * @var AdminStrategy
     */
    protected $adminStrategy;

    /**
     * 管理员信息表
     *
     * @var AdminRepository
     */
    protected $adminRepository;

    /**
     * 当前登录管理员授权信息
     *
     * @var UserAuth
     */
    protected $curUserAuth;

    /**
     * 页面标记
     *
     * @var string
     */
    protected $page_tag;

    /**
     * @var RBACBusiness
     */
    protected $rbac;

    public function inlet($returnType = SystemBaseController::RETURN_HIDE_RESOURCE, $isLogin = true)
    {
        PlatformClass::setPlatform($this->getParameter('platform_admin'));

        $this->adminRepository = $this->getDoctrine()->getRepository('App:Admin');
        $this->rbac = new RBACBusiness($this->container, PlatformClass::getPlatform());

        //菜单
        $menus = [
            new Menu('博客管理系统', null, null, null, null, [
                new Menu('统计台', 'fa fa-bar-chart', 'admin_statistical_station_index', $this->generateUrl('admin_manage_statistical_station_index'), null),
                new Menu('用户管理', 'fa fa-user-circle-o', 'admin_blog_user', $this->generateUrl('admin_users_index'), null),
                new Menu('分类管理', 'fa fa-navicon', 'admin_sort_index', $this->generateUrl('admin_manage_sort_index'), null),
                new Menu('博客管理', 'fa fa-clone', null, null, null, [
                    new Menu('文章管理', null, 'admin_article_index', $this->generateUrl('admin_blog_manage_article_index'), null),
                    new Menu('评论管理', null, 'admin_commentary_index', $this->generateUrl('admin_blog_manage_commentary_index'), null),
                    new Menu('标签管理', null, 'admin_label_index', $this->generateUrl('admin_blog_manage_label_index'), null),
                    new Menu('收藏管理', null, 'admin_collection_index', $this->generateUrl('admin_blog_manage_collection_index'), null)
                ]),
                new Menu('系统设置', 'fa fa-cog', null, null, null, [
                    new Menu('角色与权限管理', null, 'admin_role_index', $this->generateUrl('admin_manage_admin_role_index'), null),
                    new Menu('账号与角色管理', null, 'admin_user_role_index', $this->generateUrl('admin_manage_admin_user_index'), null),
                ])
            ])
        ];

        $this->adminStrategy = new AdminStrategy($this->container);

        //设置管理端基本信息(名称,页面标记,菜单......)
        $this->adminStrategy
            ->setTitle('Admin')
            ->setEntranceUrl($this->generateUrl('admin_manage_index'))
            ->setEndUrl($this->generateUrl('admin_manage_logout'))
            ->setSettingPwdUrl($this->generateUrl('admin_manage_edit_password'))
            ->setMenuModel(AdminStrategy::menu_model_simple)
            ->setPageTag($this->page_tag)
            ->setClearCacheApiUrl($this->generateUrl('admin_manage_clearCache'))
            ->setLogo($this->adminStrategy->getBaseUrl() . '/asset/logo.png')
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
                    return Responses::error(Errors::getError()->msg, -1, ['go_url' => $this->adminStrategy->getEntranceUrl()]);
                }else{
                    return $this->redirect($this->adminStrategy->getEntranceUrl());
                }
            }

            $this->adminStrategy->setAdminName(CurAuthSubject::getCurUser()->getAccount());
            $this->adminStrategy->setAdminRoleName(CurAuthSubject::getCurUser()->getName());

            $this->rbac->setIsSuper(CurAuthSubject::getCurUser()->getIsSuper());

            //对路由进行权限校验
            if(!$this->rbac->canRoute($this->get('request_stack')->getCurrentRequest()->get('_route'))){
                if(self::getReturnType() == SystemBaseController::RETURN_HIDE_RESOURCE){
                    return Responses::error('权限不足');
                }else{
                    return $this->render('@PHPZlcAdmin/page/no_permission.html.twig');
                }
            }

            //对菜单进行权限筛选
            $this->adminStrategy->setMenus($this->rbac->menusFilter($this->adminStrategy->getMenus()));
        }

        return true;

    }

    /**
     * 管理端首页
     *
     * @return RedirectResponse|Response
     */
    public function index()
    {
        $r = $this->inlet(SystemBaseController::RETURN_SHOW_RESOURCE, true);

        if($r === true){
            if(!empty(CurAuthSubject::getCurAuthSuccessGoUrl())){
                return $this->redirect(CurAuthSubject::getCurAuthSuccessGoUrl());
            }
            return $this->redirect($this->generateUrl('admin_manage_statistical_station_index'));
        }

        return $this->render('admin/auth/login.html.twig');
    }

    /**
     * 清除缓存
     *
     * @return bool|JsonResponse|RedirectResponse|Response
     */
    public function clearCache()
    {
        $r = $this->inlet();
        if($r !== true){
            return $r;
        }

        (new PermissionBusiness($this->container))->builtUpdatePermission();

        //清除当前用户权限菜单
        $this->rbac->clearCache($this->curUserAuth);

        if(Errors::isExistError()){
            return Responses::error(Errors::getError());
        }

        return Responses::success('缓存清除成功');
    }

    /**
     * 时间段筛选
     *
     * @param $at
     * @param $field
     * @return array
     */
    public function atSearch($at, $field)
    {
        $rules = [];

        if(!empty($at) && $at != 'null'){
            $at = explode(',', $at);

            if(array_key_exists(0, $at)){
                $rules[$field . Rule::RA_CONTRAST] = ['>=', $at[0]];
            }

            if(array_key_exists(1, $at)){
                $rules[$field . Rule::RA_CONTRAST_2] = ['<=', $at[1]];
            }
        }

        return $rules;
    }

}
