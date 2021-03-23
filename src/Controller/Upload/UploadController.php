<?php
/**
 * 公共接口控制层
 *
 * Created by Trick
 * user: Trick
 * Date: 2021/2/26
 * Time: 10:42 上午
 */

namespace App\Controller\Upload;

use App\Business\ProjectBusiness\ProjectDirBusiness;
use App\Business\UploadBusiness\UploadFile;
use App\Entity\Project;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Responses\Responses;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class UploadController extends AbstractController
{
    /**
     * 普通上传
     *
     * @param Request $request
     * @return false|JsonResponse
     */
    public function upload(Request $request)
    {
        $uploadName = $request->get('uploadName', 'file');
        $uploadType = $request->get('uploadType', '');

        $project_id = $request->get('project_id');

        $uploadSaveFile = 'upload';

        if(!empty($project_id)){
            /**
             * @var Project $project
             */
            $project = $this->getDoctrine()->getRepository('App:Project')->find($project_id);
            if(!empty($project)){
                $uploadSaveFile = ProjectDirBusiness::getProjectDir($project);
            }else{
                return Responses::error('项目不存在');
            }
        }

        $uploadFile = new UploadFile($this->container);

        $r = $uploadFile->upload($uploadName, $uploadSaveFile, $uploadType);

        if($r === false){
            return Responses::error(Errors::getError());
        }

        return Responses::success('上传成功', $r);
    }
}