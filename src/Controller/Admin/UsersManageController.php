<?php
/**
 * 博客用户管理
 *
 * Created by Trick
 * user: Trick
 * Date: 2021/3/30
 * Time: 4:59 下午
 */

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use PHPZlc\Admin\Strategy\Navigation;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use PHPZlc\PHPZlc\Responses\Responses;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;


class UsersManageController extends AdminManageController
{
    protected $page_tag = 'admin_blog_user';

    /**
     * @var UserRepository
     */
    protected $userRepository;

    public function inlet($returnType = SystemBaseController::RETURN_HIDE_RESOURCE, $isLogin = true)
    {
        $r = parent::inlet($returnType, $isLogin);
        if($r !== true){
            return $r;
        }

        $this->adminStrategy->addNavigation(new Navigation('用户管理'));

        $this->userRepository = $this->getDoctrine()->getRepository('App:User');

        return true;
    }

    /**
     * 用户管理首页
     *
     * @param Request|null $request
     * @return bool|JsonResponse|RedirectResponse|Response
     */
    public function index(Request $request = null)
    {
        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, true);
        if($r !== true){
            return $r;
        }

        $this->adminStrategy->setUrlAnchor();

        $user_name = $request->get('user_name');
        $mailbox = $request->get('mailbox');

        $rules = [
          'user_name' . Rule::RA_LIKE => '%' . $user_name . '%',
          'mailbox' . Rule::RA_LIKE => '%' . $mailbox . '%'
        ];

        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);

        $data = $this->userRepository->findLimitAll($rows, $page, $rules);
        $count = $this->userRepository->findCount($rules);

        return $this->render('admin/user/index.html.twig', array(
            'page' => $page,
            'rows' => $rows,
            'count' => $count
        ));
    }

    /**
     * 用户禁用
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse
     */
    public function disable(Request $request)
    {
        $r = $this->inlet(self::RETURN_HIDE_RESOURCE, true);
        if($r !== true){
            return $r;
        }

        $id = $request->get('id');

        $user = $this->userRepository->find($id);
        $user->setIsDisable(true);

        $this->getDoctrine()->getManager()->flush();
        $this->getDoctrine()->getManager()->clear();

        return Responses::success('禁用成功');
    }

    /**
     * 用户启用
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse
     */
    public function enable(Request $request)
    {
        $r = $this->inlet(self::RETURN_HIDE_RESOURCE, true);
        if($r !== true){
            return $r;
        }

        $id = $request->get('id');

        $user = $this->userRepository->find($id);
        $user->setIsDisable(false);

        $this->getDoctrine()->getManager()->flush();
        $this->getDoctrine()->getManager()->clear();

        return Responses::success('启用成功');
    }
}