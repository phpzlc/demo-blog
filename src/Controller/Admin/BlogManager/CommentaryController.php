<?php
/**
 * 评论管理
 *
 * Created by Trick
 * user: Trick
 * Date: 2021/4/6
 * Time: 9:50 上午
 */

namespace App\Controller\Admin\BlogManager;

use App\Controller\Admin\AdminController;
use App\Repository\CommentaryRepository;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use PHPZlc\PHPZlc\Responses\Responses;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentaryController extends AdminController
{
    protected $page_tag = 'admin_commentary_index';

    /**
     * @var CommentaryRepository
     */
    protected $commentaryRepository;

    public function inlet($returnType = SystemBaseController::RETURN_HIDE_RESOURCE, $isLogin = true)
    {
        $r = parent::inlet($returnType, $isLogin);
        if($r !== true){
            return $r;
        }

        $this->commentaryRepository = $this->getDoctrine()->getRepository('App:Commentary');

        return true;
    }

    /**
     * 评论管理-首页
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
        $create_at = $request->get('create_at');

        $rules = [
            Rule::R_SELECT => 'sql_pre.*, u.subject_name, a.title',
            'user' . Rule::RA_JOIN => array(
                'alias' => 'u'
            ),
            'u.subject_name' .  Rule::RA_LIKE => '%' . $user_name . '%',
            'a.title' . Rule::RA_LIKE => '%' . $article_title . '%',
            'article' . Rule::RA_JOIN => array(
                'alias' => 'a'
            ),
        ];

        $rules = array_merge($rules, $this->atSearch($create_at, 'create_at'));

        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);

        $data = $this->commentaryRepository->findLimitAll($rows, $page, $rules);
        $count = $this->commentaryRepository->findCount($rules);

        return $this->render('admin/blog/commentary/index.html.twig', array(
            'page' => $page,
            'rows' => $rows,
            'count' => $count,
            'data' => $data
        ));
    }

    /**
     * 评论管理-删除
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

        $commentary = $this->commentaryRepository->find($id);

        if(empty($commentary)){
            return Responses::error('未找到该评论');
        }

        $commentary->setIsDel(true);

        $this->getDoctrine()->getManager()->flush();

        return Responses::success('删除成功');
    }
}

