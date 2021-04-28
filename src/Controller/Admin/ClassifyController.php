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

use App\Business\ClassifyBusiness\ClassifyBusiness;
use App\Entity\Classify;
use App\Repository\ClassifyRepository;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use PHPZlc\PHPZlc\Responses\Responses;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ClassifyController extends AdminManageController
{
    protected $page_tag = 'admin_sort_index';

    /**
     * @var ClassifyRepository
     */
    protected $classifyRepository;

    /**
     * @var ClassifyBusiness
     */
    protected $classifyBusiness;

    public function inlet($returnType = SystemBaseController::RETURN_HIDE_RESOURCE, $isLogin = true)
    {
        $r = parent::inlet($returnType, $isLogin);
        if($r !== true){
            return $r;
        }

        $this->classifyRepository = $this->getDoctrine()->getRepository('App:Classify');
        $this->classifyBusiness = new ClassifyBusiness($this->container);

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

        $classify_no = $request->get('classify_no');
        $classify_name = $request->get('classify_name');

        $rules = [
            'classifyNo' . Rule::RA_LIKE => '%' . $classify_no . '%',
            'classifyName' . Rule::RA_LIKE => '%' . $classify_name . '%'
        ];

        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);

        $data = $this->classifyRepository->findLimitAll($rows, $page, $rules);
        $count = $this->classifyRepository->findCount($rules);

        return $this->render('admin/sort/index.html.twig', array(
            'page' => $page,
            'rows' => $rows,
            'count' => $count,
            'data' => $data
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
        $classify = null;

        if(!empty($id)){
            $classify = $this->classifyRepository->find($id);
        }

        return $this->render('admin/sort/edit.html.twig', array(
            'classify' => $classify,
        ));
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

        $classify_no = $request->get('classify_no');
        $classify_name = $request->get('classify_name');

        $sort = new Classify();
        $sort->setClassifyNo($classify_no);
        $sort->setClassifyName($classify_name);

        if(!$this->classifyBusiness->create($sort)){
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
        $classify_no = $request->get('classify_no');
        $classify_name = $request->get('classify_name');


        $sort = $this->classifyRepository->find($id);

        if(empty($sort)){
            return Responses::error('未找到该分类');
        }

        $sort->setClassifyNo($classify_no);
        $sort->setClassifyName($classify_name);

        if(!$this->classifyBusiness->update($sort)){
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

        $classify = $this->classifyRepository->find($id);

        if(empty($classify)){
            return Responses::error('未找到该分类');
        }

        $classify->setIsDel(true);

        $this->getDoctrine()->getManager()->flush();

        return Responses::success('删除成功');
    }
}