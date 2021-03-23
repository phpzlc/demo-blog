<?php
/**
 * Upload
 *
 * @author      Josh Lockhart <info@joshlockhart.com>
 * @copyright   2012 Josh Lockhart
 * @link        http://www.joshlockhart.com
 * @version     1.0.0
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace PHPZlc\Upload;

/**
 * File
 *
 * This class provides the implementation for an uploaded file. It exposes
 * common attributes for the uploaded file (e.g. name, extension, media type)
 * and allows you to attach validations to the file that must pass for the
 * upload to succeed.
 *
 * @author  Josh Lockhart <info@joshlockhart.com>
 * @since   1.0.0
 * @package Upload
 */
class File extends \SplFileInfo
{
    /********************************************************************************
    * Static Properties
    *******************************************************************************/

    /**
     * Upload error code messages
     * @var array
     */
    protected static $errorCodeMessages = array(
        1 => '上传资源超出服务器设置！',  // 上传的文件在php.ini中超过upload_max_filesize指令
        2 => '上传资源超出系统配置!', // 上传的文件超过max_file_size指令是在HTML表单中指定的
        3 => '上传资源部分被上传了!', //上传的文件只是部分上传。
        4 => '没有资源上传!', // 没有上传文件
        6 => '丢失了临时文件！', //缺少临时文件夹
        7 => '不能写入磁盘！', // 无法将文件写入磁盘
        8 => '不支持拓展上传！' // PHP扩展停止了文件上传
    );

    /**
     * Lookup hash to convert file units to bytes
     * @var array
     */
    protected static $units = array(
        'b' => 1,
        'k' => 1024,
        'm' => 1048576,
        'g' => 1073741824
    );

    /********************************************************************************
    * Instance Properties
    *******************************************************************************/

    /**
     * Storage delegate
     * @var \PHPZlc\Upload\Storage\Base
     */
    protected $storage;

    /**
     * Validations
     * @var array[\PHPZlc\Upload\Validation\Base]
     */
    protected $validations;

    /**
     * Validation errors
     * @var array
     */
    protected $errors;

    /**
     * Original file name provided by client (for internal use only)
     * @var string
     */
    protected $originalName;

    /**
     * File name (without extension)
     * @var string
     */
    protected $name;

    /**
     * File extension (without leading dot)
     * @var string
     */
    protected $extension;

    /**
     * File mimetype (e.g. "image/png")
     * @var string
     */
    protected $mimetype;

    /**
     * Upload error code (for internal use only)
     * @var  int
     * @link http://www.php.net/manual/en/features.file-upload.errors.php
     */
    protected $errorCode;

    /**
     * Constructor
     * @param  string                            $key            The file's key in $_FILES superglobal
     * @param  \PHPZlc\Upload\Storage\Base              $storage        The method with which to store file
     * @throws \PHPZlc\Upload\Exception\UploadException If file uploads are disabled in the php.ini file
     * @throws \InvalidArgumentException         If $_FILES key does not exist
     */
    public function __construct($key, \PHPZlc\Upload\Storage\Base $storage)
    {
        if (!isset($_FILES[$key])) {
            throw new \InvalidArgumentException("Cannot find uploaded file identified by key: $key");
        }
        $this->storage = $storage;
        $this->validations = array();
        $this->errors = array();
        $this->originalName = $_FILES[$key]['name'];
        $this->errorCode = $_FILES[$key]['error'];
        parent::__construct($_FILES[$key]['tmp_name']);
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        if (!isset($this->name)) {
            $this->name = $this->path_info($this->originalName, PATHINFO_FILENAME);
        }

        return $this->name;
    }

    /**
     * Set name (without extension)
     * @param  string           $name
     * @return \PHPZlc\Upload\File     Self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get file name with extension
     * @return string
     */
    public function getNameWithExtension()
    {
        return sprintf('%s.%s', $this->getName(), $this->getExtension());
    }

    /**
     * Get file extension (without leading dot)
     * @return string
     */
    public function getExtension()
    {
        if (!isset($this->extension)) {
            $this->extension = strtolower($this->path_info($this->originalName, PATHINFO_EXTENSION));
        }

        return $this->extension;
    }

    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * Get mimetype
     * @return string
     */
    public function getMimetype()
    {
        if (empty($this->mimeType)) {
            $finfo = new \finfo(FILEINFO_MIME);
            $mimetype = $finfo->file($this->getPathname());
            $mimetypeParts = preg_split('/\s*[;,]\s*/', $mimetype);
            $this->mimetype = strtolower($mimetypeParts[0]);
            unset($finfo);
        }

        return $this->mimetype;
    }

    /**
     * Get md5
     * @return string
     */
    public function getMd5()
    {
        return md5_file($this->getPathname());
    }

    /**
     * Get image dimensions
     * @return array formatted array of dimensions
     */
    public function getDimensions()
    {
        list($width, $height) = getimagesize($this->getPathname());
        return array(
            'width' => $width,
            'height' => $height
        );
    }

    /********************************************************************************
    * Validate
    *******************************************************************************/

    /**
     * Add file validations
     * @param \PHPZlc\Upload\Validation\Base|array[\PHPZlc\Upload\Validation\Base] $validations
     */
    public function addValidations($validations)
    {
        if (!is_array($validations)) {
            $validations = array($validations);
        }
        foreach ($validations as $validation) {
            if ($validation instanceof \PHPZlc\Upload\Validation\Base) {
                $this->validations[] = $validation;
            }
        }
    }

    /**
     * Get file validations
     * @return array[\PHPZlc\Upload\Validation\Base]
     */
    public function getValidations()
    {
        return $this->validations;
    }

    /**
     * Validate file
     * @return bool True if valid, false if invalid
     */
    public function validate()
    {
        // Validate is uploaded OK
        if ($this->isOk() === false) {
            $this->errors[] = self::$errorCodeMessages[$this->errorCode];
        }

        // Validate is uploaded file
        if ($this->isUploadedFile() === false) {
            $this->errors[] = 'The uploaded file was not sent with a POST request';
        }

        // User validations
        foreach ($this->validations as $validation) {
            if ($validation->validate($this) === false) {
                $this->errors[] = $validation->getMessage();
            }
        }

        return empty($this->errors);
    }

    /**
     * Get file validation errors
     * @return array[String]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Add file validation error
     * @param  string
     * @return \PHPZlc\Upload\File Self
     */
    public function addError($error)
    {
        $this->errors[] = $error;

        return $this;
    }

    /********************************************************************************
    * Upload
    *******************************************************************************/

    /**
     * Upload file (delegated to storage object)
     * @param  string $newName Give the file it a new name
     * @return bool
     * @throws \PHPZlc\Upload\Exception\UploadException If file does not validate
     */
    public function upload($newName = null)
    {
        if ($this->validate() === false) {
            throw new \PHPZlc\Upload\Exception\UploadException('File validation failed');
        }

        // Update the name, leaving out the extension
        if (is_string($newName)) {
            $this->name = $this->path_info($newName, PATHINFO_FILENAME);
        }

        return $this->storage->upload($this, $newName);
    }

    /********************************************************************************
    * Helpers
    *******************************************************************************/

    /**
     * Is this file uploaded with a POST request?
     *
     * This is a separate method so that it can be stubbed in unit tests to avoid
     * the hard dependency on the `is_uploaded_file` function.
     *
     * @return  bool
     */
    public function isUploadedFile()
    {
        return is_uploaded_file($this->getPathname());
    }

    /**
     * Is this file OK?
     *
     * This method inspects the upload error code to see if the upload was
     * successful or if it failed for a variety of reasons.
     *
     * @link    http://www.php.net/manual/en/features.file-upload.errors.php
     * @return  bool
     */
    public function isOk()
    {
        return ($this->errorCode === UPLOAD_ERR_OK);
    }

    /**
     * Convert human readable file size (e.g. "10K" or "3M") into bytes
     * @param  string $input
     * @return int
     */
    public static function humanReadableToBytes($input)
    {
        $number = (int)$input;
        $unit = strtolower(substr($input, -1));
        if (isset(self::$units[$unit])) {
            $number = $number * self::$units[$unit];
        }

        return $number;
    }

    public function path_info($filepath, $key = '')
    {
        $path_parts = array();
        $path_parts['dirname'] = rtrim(substr($filepath, 0, strrpos($filepath, '/')),'/').'/';
        $path_parts['basename'] = ltrim(substr($filepath, strrpos($filepath, '/')),'/');
        $path_parts['extension'] = substr(strrchr($filepath, '.'), 1);
        $path_parts['filename'] = ltrim(substr($path_parts ['basename'], 0, strrpos($path_parts ['basename'], '.')),'/');

//        if ($key && isset($path_parts[$key])) {
//
//            return $path_parts[$key];
//        } else {
//
//            return $path_parts;
//        }

        switch ($key) {
            case 1:
                $param = 'dirname';
                break;
            case 2:
                $param = 'basename';
                break;
            case 4:
                $param = 'extension';
                break;
            case 8:
                $param = 'filename';
                break;
        }

        return $path_parts[$param];
    }
}
