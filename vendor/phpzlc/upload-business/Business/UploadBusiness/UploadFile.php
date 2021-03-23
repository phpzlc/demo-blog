<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/8/10
 */

namespace App\Business\UploadBusiness;

use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Business\AbstractBusiness;
use PHPZlc\PHPZlc\Bundle\Service\Log\Log;
use PHPZlc\Upload\File;
use Psr\Container\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

class UploadFile extends AbstractBusiness
{
    //图片
    const TYPE_IMAGE = 1;

    //视频
    const TYPE_VIDEO = 2;

    //音频
    const TYPE_AUDIO = 3;

    //文件
    const TYPE_FILE = 4;

    //压缩包
    const TYPE_ARCHIVE = 5;

    //表格
    const TYPE_EXCEL= 6;

    //全部
    const TYPE_ALL = 7;

    protected $fileType;

    public function getFileType()
    {
        $fileTypes = array(
            self::TYPE_IMAGE => array(
                'types' => array('image/png', 'image/gif', 'image/jpg', 'image/jpeg', 'image/x-icon'),
                'size' => '10M',
                'title' => '图片'
            ),
            self::TYPE_VIDEO => array(
                'types' => array('video/mp4','video/x-msvideo','video/3gpp'),
                'size' => '1024M',
                'title' => '视频'
            ),
            self::TYPE_AUDIO => array(
                'types' => array('audio/mpeg'),
                'size' => '100M',
                'title' => '音频'
            ),
            self::TYPE_FILE => array(
                'types' => array('application/msword', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint',
                    'application/pdf', 'application/octet-stream','application/doc','application/zip','application/excel',
                    'application/rar','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
                'size' => '100M',
                'title' => '文件'
            ),
            self::TYPE_ARCHIVE => array(
                'types' => array('application/zip', 'application/rar', 'application/x-rar'),
                'size' => '100M',
                'title' => '压缩文件'
            ),
            self::TYPE_EXCEL => array(
                'types' => array('application/vnd.ms-excel','application/excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/vnd.ms-office', 'application/zip', 'application/octet-stream'),
                'size' => '100M',
                'title' => '表格'
            )
        );

        $types = array();

        foreach ($fileTypes as $k => $v){
            $types = array_merge($types, $v['types']);
        }

        $fileTypes[self::TYPE_ALL] = array(
            'types' => $types,
            'size' => '500M',
            'title' => '全部'
        );

        return  $fileTypes;
    }

    public static function getPublicDir()
    {
        return dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'public';
    }

    public function upload($inputName, $relatively_path = null, $fileType = self::TYPE_IMAGE, $save_name = null)
    {
        $this->fileType = $this->getFileType();

        if(!(isset($_FILES[$inputName]) && $_FILES[$inputName]['name'])){
            Errors::setErrorMessage('上传失败,未上传资源');
            return false;
        }


        if(!isset($this->fileType[$fileType])){
            Errors::setErrorMessage('上传失败,上传类型未定义');
            return false;
        }

        if(empty($relatively_path)){
            $relatively_path = 'upload';
        }

        $path = self::getPublicDir() . DIRECTORY_SEPARATOR . $relatively_path;

        $fileSystem = new Filesystem();
        if(!$fileSystem->exists($path)){
            $fileSystem->mkdir($path);
        }

        $uploadFileSystem = new \PHPZlc\Upload\Storage\FileSystem($path);

        $file = new File($inputName, $uploadFileSystem);
        $file->setName(empty($save_name) ? uniqid() : $save_name);

        if(empty($file->getPathname())){
            Errors::setErrorMessage('上传失败,' . $this->fileType[$fileType]['title'] . '过大');
            return false;
        }else{
            $fileSystem->chmod($file->getPathname(), 0777);
        }

        $file->addValidations(array(
            //验证文件类型  MimeType List => http://www.iana.org/assignments/media-types/media-types.xhtml
            new \PHPZlc\Upload\Validation\Mimetype($this->fileType[$fileType]['types']),
            //验证文件大小  use "B", "K", M", or "G"
            new \PHPZlc\Upload\Validation\Size($this->fileType[$fileType]['size']),
        ));

        try {
            $data = array(
                'path' =>  $relatively_path . DIRECTORY_SEPARATOR . $file->getNameWithExtension(),
                'name'       => $file->getNameWithExtension(),
                'extension'  => $file->getExtension(),
                'mime'       => $file->getMimetype(),
                'size'       => $file->getSize(),
                'md5'        => $file->getMd5(),
                'dimensions' => $file->getDimensions(),
                'original_name' => $file->getOriginalName(),
                'server_path' => self::getFileNetworkPath($this->container, $relatively_path . DIRECTORY_SEPARATOR . $file->getNameWithExtension())
            );

            $file->upload();

            return $data;
        }catch (\Exception $exception){
            // Fail!
            $errorMessages = $file->getErrors();

            if(empty($errorMessages)){
                $errorMessages = $exception->getMessage();
            }else{
                $errorMessages = $errorMessages[0];
            }

            Log::writeLog('文件上传错误' . $errorMessages . '文件允许类型' . $this->fileType[$fileType]['title'] . '文件本身类型' . $file->getMimetype());

            Errors::setErrorMessage($this->fileType[$fileType]['title'] . '上传失败');
            return false;
        }
    }

    public static function getFileNetworkPath(ContainerInterface $container, $path)
    {
        return $container->get('request_stack')->getCurrentRequest()->getSchemeAndHttpHost() . $container->get('request_stack')->getCurrentRequest()->getBasePath() . '/' . $path;
    }
}