<?php
/**
 * 分类管理
 *
 * Created by Trick
 * user: Trick
 * Date: 2021/4/12
 * Time: 5:32 下午
 */

namespace App\Controller\Admin;

use App\Business\SortBusiness\SortBusiness;
use App\Entity\Sort;
use App\Repository\SortRepository;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use PHPZlc\PHPZlc\Responses\Responses;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class SortManageController extends AdminManageController
{
    protected $page_tag = 'admin_sort_index';

    /**
     * @var SortRepository
     */
    protected $sortRepository;

    /**
     * @var SortBusiness
     */
    protected $sortBusiness;

    public function inlet($returnType = SystemBaseController::RETURN_HIDE_RESOURCE, $isLogin = true)
    {
        $r = parent::inlet($returnType, $isLogin);
        if($r !== true){
            return $r;
        }

        $this->sortRepository = $this->getDoctrine()->getRepository('App:Sort');
        $this->sortBusiness = new SortBusiness($this->container);

        return true;
    }

    /**
     * 分类管理-首页
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

        $sort_no = $request->get('sort_no');
        $sort_name = $request->get('sort_name');

        $rules = [
            'sortNo' . Rule::RA_LIKE => '%' . $sort_no . '%',
            'sortName' . Rule::RA_LIKE => '%' . $sort_name . '%'
        ];

        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);

        $data = $this->sortRepository->findLimitAll($rows, $page, $rules);
        $count = $this->sortRepository->findCount($rules);

        return $this->render('admin/sort/index.html.twig', array(
            'page' => $page,
            'rows' => $rows,
            'count' => $count,
            'sorts' => $this->sortRepository->sequenceSorts($data)
        ));
    }

    /**
     * 分类管理-新建/编辑页面
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

        $id = $request->get('id');

        return $this->render('admin/sort/edit.html.twig', array());
    }

    /**
     * 分类管理-新建
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

        $sort_no = $request->get('sort_no');
        $sort_name = $request->get('sort_name');
        $parentSortId = $request->get('parentSortId');

        $sort = new Sort();
        $sort->setSortNo($sort_no);
        $sort->setSortName($sort_name);
        $sort->setParentSort($this->sortRepository->find($parentSortId));

        if(!$this->sortBusiness->create($sort)){
            return Responses::error(Errors::getError());
        }

        return Responses::success('新建成功');
    }

    /**
     * 分类管理-编辑
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
        $sort_no = $request->get('sort_no');
        $sort_name = $request->get('sort_name');
        $parentSortId = $request->get('parentSortId');

        $sort = $this->sortRepository->find($id);

        if(!empty($sort)){
            return Responses::error('未找到该分类');
        }

        $sort->setSortNo($sort_no);
        $sort->setSortName($sort_name);
        $sort->setParentSort($this->sortRepository->find($parentSortId));

        if(!$this->sortBusiness->update($sort)){
            return Responses::error(Errors::getError());
        }

        return Responses::success('修改成功');
    }

    /**
     * 分类管理-删除
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

        $sort = $this->sortRepository->find($id);

        if(empty($sort)){
            return Responses::error('未找到该分类');
        }

        $sort->setIsDel(true);

        $this->getDoctrine()->getManager()->flush();

        return Responses::success('删除成功');
    }
}