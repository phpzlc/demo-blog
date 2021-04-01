<?php
/**
 * 文章管理
 *
 * Created by Trick
 * user: Trick
 * Date: 2021/4/1
 * Time: 10:52 上午
 */

namespace App\Controller\Admin\BlogManager;

use App\Controller\Admin\AdminManageController;
use App\Repository\ArticleRepository;
use PHPZlc\Admin\Strategy\Navigation;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;


class ArticleManagerController extends AdminManageController
{
    protected $page_tag = 'admin_article_index';

    /**
     * @var ArticleRepository
     */
    protected $articleRepository;

    public function inlet($returnType = SystemBaseController::RETURN_HIDE_RESOURCE, $isLogin = true)
    {
        $r = parent::inlet($returnType, $isLogin);
        if($r !== true){
            return $r;
        }

        $this->adminStrategy->addNavigation(new Navigation('文章管理'));

        $this->articleRepository = $this->getDoctrine()->getRepository('App:Article');

        return true;
    }

    /**
     * 博客-文章管理首页
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
        $title = $request->get('title');

        $rules = [
          'title' . Rule::RA_LIKE => '%' . $title . '%',
          'userAuth' . Rule::RA_LIKE => '%' . $user_name . '%'
        ];

        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);

        $data = $this->articleRepository->findLimitAll($rows, $page, $rules);
        $count = $this->articleRepository->findCount($rules);

        return $this->render('admin/blog/article/index.html.twig', array(
           'page' => $page,
           'rows' => $rows,
           'count' => $count
        ));

    }

    /**
     * 博客-文章新建/编辑页面
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse|Response
     */
    public function page(Request $request)
    {
        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, true);
        if($r !== true){
            return $r;
        }

        return $this->render('admin/blog/article/edit.html.twig');
    }


}