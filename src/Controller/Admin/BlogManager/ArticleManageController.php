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

use App\Business\ArticleBusiness\ArticleBusiness;
use App\Business\AuthBusiness\CurAuthSubject;
use App\Controller\Admin\AdminManageController;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use PHPZlc\Admin\Strategy\Navigation;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use PHPZlc\PHPZlc\Responses\Responses;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;


class ArticleManageController extends AdminManageController
{
    protected $page_tag = 'admin_article_index';

    /**
     * @var ArticleRepository
     */
    protected $articleRepository;

    /**
     * @var ArticleBusiness
     */
    protected $articleBusiness;

    public function inlet($returnType = SystemBaseController::RETURN_HIDE_RESOURCE, $isLogin = true)
    {
        $r = parent::inlet($returnType, $isLogin);
        if($r !== true){
            return $r;
        }

        $this->adminStrategy->addNavigation(new Navigation('文章管理'));

        $this->articleRepository = $this->getDoctrine()->getRepository('App:Article');
        $this->articleBusiness = new ArticleBusiness($this->container);

        return true;
    }

    /**
     * 文章管理-首页
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
     * 文章管理-新建/编辑页面
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

    /**
     * 文章管理-新建
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse
     */
    public function create(Request $request)
    {
        $r = $this->inlet(self::RETURN_HIDE_RESOURCE, true);
        if($r !== true){
            return $r;
        }

        $title = $request->get('title');
        $content = $request->get('content');

        $article = new Article();
        $article->setTitle($title)
            ->setContent($content)
            ->setUserAuth(CurAuthSubject::getCurUserAuth());

        if(!$this->articleBusiness->create($article)){
            return Responses::error(Errors::getError());
        }

        return Responses::success('发布成功');
    }

    /**
     * 文章管理-编辑
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse
     */
    public function edit(Request $request)
    {
        $r = $this->inlet(self::RETURN_HIDE_RESOURCE, true);
        if($r !== true){
            return $r;
        }

        $id = $request->get('id');
        $title = $request->get('title');
        $content = $request->get('content');

        $article = $this->articleRepository->find($id);
        $article->setTitle($title)
            ->setContent($content);

        if(!$this->articleBusiness->update($article)){
            return Responses::error(Errors::getError());
        }

        return Responses::success('修改成功');
    }

    /**
     * 文章管理-删除
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse
     */
    public function delete(Request $request)
    {
        $r = $this->inlet(self::RETURN_HIDE_RESOURCE, true);
        if($r !== true){
            return $r;
        }

        $id = $request->get('id');

        $article = $this->articleRepository->find($id);

        if(empty($article)){
            return Responses::error('没有该文章');
        }

        $article->setIsDel(true);

        $this->getDoctrine()->getManager()->flush();
        $this->getDoctrine()->getManager()->clear();

        return Responses::success('删除成功');
    }


}