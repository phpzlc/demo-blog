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
use App\Entity\UserAuth;
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

    public function inlet($returnType = SystemBaseController::RETURN_HIDE_RESOURCE, $isLogin = true)
    {
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