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

use App\Controller\Admin\AdminController;
use App\Repository\CollectionRepository;
use PHPZlc\Admin\Strategy\Navigation;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class CollectionController extends AdminController
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

        $this->adminStrategy->addNavigation(new Navigation('收藏管理'));

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

        $rules = [
            Rule::R_SELECT => "sql_pre.*, ua.subject_name, a.id as a_id, a.title",
            'ua.subject_name' .  Rule::RA_LIKE => '%' . $user_name . '%',
            'a.title' . Rule::RA_LIKE => '%' . $article_title . '%',
            'article' . Rule::RA_JOIN => array(
                'alias' => 'a'
            ),
            'userAuth' . Rule::RA_JOIN => array(
                'alias' => 'ua'
            ),
        ];

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