<?php
/**
 * 标签管理
 *
 * Created by Trick
 * user: Trick
 * Date: 2021/4/6
 * Time: 11:12 上午
 */

namespace App\Controller\Admin\BlogManager;

use App\Business\LabelBusiness\LabelBusiness;
use App\Controller\Admin\AdminManageController;
use App\Entity\Label;
use App\Repository\LabelRepository;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use PHPZlc\PHPZlc\Responses\Responses;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class LabelManageController extends AdminManageController
{
    protected $page_tag = 'admin_label_index';

    /**
     * @var LabelRepository
     */
    protected $labelRepository;

    /**
     * @var LabelBusiness
     */
    protected $labelBusiness;

    public function inlet($returnType = SystemBaseController::RETURN_HIDE_RESOURCE, $isLogin = true)
    {
        $r = parent::inlet($returnType, $isLogin);
        if($r !== true){
            return $r;
        }

        $this->labelRepository = $this->getDoctrine()->getRepository('App:Label');
        $this->labelBusiness = new LabelBusiness($this->container);

        return true;
    }

    /**
     * 标签管理-首页
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

        $name = $request->get('name');

        $rules = [];

        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);

        $data = $this->labelRepository->findLimitAll($rows, $page, $rules);
        $count = $this->labelRepository->findCount($rules);

        return $this->render('admin/blog/label/index.html.twig', array(
            'page' => $page,
            'rows' => $rows,
            'count' => $count,
            'data' => $data
        ));
    }

    /**
     * 标签管理-新建/编辑页面
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

        return $this->render('admin/blog/label/edit.html.twig');
    }

    /**
     * 标签管理-新建
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

        $name = $request->get('name');
        $describe = $request->get('describe');

        $label = new Label();
        $label->setName($name)
            ->setDescribe($describe);

        if(!$this->labelBusiness->create($label)){
            return Responses::error(Errors::getError());
        }

        return Responses::success('新建成功');
    }

    /**
     * 标签管理-编辑
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
        $name = $request->get('name');
        $describe = $request->get('describe');

        $label = $this->labelRepository->find($id);

        if(empty($label)){
            return Responses::error('标签未找到');
        }

        $label->setName($name)
            ->setDescribe($describe);

        if(!$this->labelBusiness->update($label)){
            return Responses::error(Errors::getError());
        }

        return Responses::success('编辑成功');
    }

    /**
     * 标签管理-删除
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

        $label = $this->labelRepository->find($id);

        if(empty($label)){
            return Responses::error('标签未找到');
        }

        $label->setIsDel(true);

        $this->getDoctrine()->getManager()->flush();
        $this->getDoctrine()->getManager()->clear();

        return Responses::success('删除成功');
    }

}