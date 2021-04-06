<?php
/**
 * 收藏管理
 *
 * Created by Trick
 * user: Trick
 * Date: 2021/4/6
 * Time: 3:00 下午
 */

namespace App\Controller\Admin\BlogManager;

use App\Controller\Admin\AdminManageController;
use App\Repository\CollectionRepository;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class CollectionManageController extends AdminManageController
{
    protected $page_tag = 'admin_collection_index';

    /**
     * @var CollectionRepository
     */
    protected $collectionRepository;

    public function inlet($returnType = SystemBaseController::RETURN_HIDE_RESOURCE, $isLogin = true)
    {
        $r = parent::inlet($returnType, $isLogin);
        if($r !== true){
            return $r;
        }

        $this->collectionRepository = $this->getDoctrine()->getRepository('App:Collection');

        return true;
    }

    /**
     * 收藏管理-首页
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

        $user_name = $request->get('user_name');
        $article_title = $request->get('article_title');

        $rules = [];

        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);

        $data = $this->collectionRepository->findLimitAll($rows, $page, $rules);
        $count = $this->collectionRepository->findCount($rules);

        return $this->render('admin/blog/collection/index.html.twig', array(
           'page' => $page,
           'rows' => $rows,
           'count' => $count,
           'data' => $data
        ));
    }
}