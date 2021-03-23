<?php

namespace PHPZlc\Document;

use Doctrine\Common\Annotations\Annotation\Enum;

class Document
{
    private static $documents = array();

    private static $item = 0;

    public function add()
    {
        $debug_backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];

        return $this->set('physical_address', $debug_backtrace['class'] . '::' . $debug_backtrace['function'] . ' ' . $debug_backtrace['line'] . '行');
    }

    /**
     * Api设置
     *
     * @param array $config
     *      config_item:
     *          is_hide : 是否隐藏
     *          is_host : 是否本域
     * @return $this
     * @throws \Exception
     */
    public function config($config = array())
    {
        foreach ($config as $key => $value){
            if(!in_array($key, array('is_hide', 'is_host'))){
                $this->Exception('config_item' , $key, '设置项 "is_hide", "is_host" 中选择');
            }

            if($value !== true && $value !== false){
                $this->Exception($key, $value, '设置项可在 "true", "false" 中选择');
            }

            $this->set($key, $value);
        }

        return $this;
    }

    public function generate()
    {
        if(!array_key_exists('title', $this->getLocalDocument()) || empty($this->getLocalDocument()['title'])){
            $this->Exception('title', '', '标题不能为空');
        }

        if(!array_key_exists('url', $this->getLocalDocument()) || empty($this->getLocalDocument()['url'])){
            $this->Exception('url', '', ' 请求地址不能为空');
        }

        if(!array_key_exists('method', $this->getLocalDocument())){
            $this->setMethod('get');
        }

        if(!array_key_exists('return_type', $this->getLocalDocument())){
            $this->setReturnType('json');
        }

        if(!array_key_exists('is_hide', $this->getLocalDocument())){
            $this->set('is_hide', false);
        }

        if(!array_key_exists('is_host', $this->getLocalDocument())){
            $this->set('is_host', true);
        }

        if(!array_key_exists('group', $this->getLocalDocument())){
            $this->setGroup('');
        }

        if(!array_key_exists('explain', $this->getLocalDocument())){
            $this->setExplain('');
        }

        if(!array_key_exists('return', $this->getLocalDocument())){
            $this->setReturn('');
        }

        if(!array_key_exists('param', $this->getLocalDocument())){
            $this->set('param', array());
        }else{
            $params = $this->get('param');

            foreach ($params as &$param)
            {
                if(empty($param['method'])){
                    $param['method'] = $this->get('method');
                }
            }

            $this->set('param', $params);
        }

        self::$item ++;
    }

    public function set($key, $value)
    {
        self::$documents[self::$item][$key] = $value;

        return $this;
    }

    private function get($key)
    {
        return self::$documents[self::$item][$key];
    }

    private function getLocalDocument()
    {
        return self::$documents[self::$item];
    }

    public function getDocuments()
    {
        return self::$documents;
    }

    /**
     * 抛出异常
     *
     * @param $key
     * @param $value
     * @param $explain
     * @throws \Exception
     */
    private function Exception($key , $value, $explain)
    {
        throw new \Exception($this->get('physical_address') .' Param '. $key .' : "' . $value  . '" 出错。 ' . $explain . '。');
    }

    /**
     * 设置标题
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->set('title', $title);
    }

    /**
     * 设置分组
     *
     * @param string $group  分组用/分离 例如 用户端/登陆模块
     * @return $this
     */
    public function setGroup($group)
    {
        return $this->set('group', $group);
    }

    /**
     * 设置请求地址
     *
     * @param string $url  如果是拼接地址则以/开头 例如 /service/area/all
     * @return $this
     */
    public function setUrl($url)
    {
        return $this->set('url', $url);
    }

    /**
     * 设置说明
     *
     * @param $explain
     * @return $this
     */
    public function setExplain($explain)
    {
        return $this->set('explain', $explain);
    }
    /**
     * 设置请求方式
     *
     * @param Enum({"get", "post"}) $method
     * @return $this
     * @throws \Exception
     */
    public function setMethod($method)
    {
        $key = 'method';

        if(!in_array($method , array('get', 'post'))){
            $this->Exception($key, $method, '设置项可在 "get", "post" 中选择');
        }

        return $this->set($key, $method);
    }

    /**
     * 设置返回值类型
     *
     * @param Enum({"json", "file", "html", "xml", "image"}) 返回值类型
     * @return $this
     * @throws \Exception
     */
    public function setReturnType($return_type)
    {
        $key = 'return_type';

        if(!in_array($return_type , array("json", "file", "html", "xml", "image"))){
            $this->Exception($key, $return_type, '设置项可在 "json", "file", "html", "xml", "image" 中选择');
        }

        return $this->set($key, $return_type);
    }

    /**
     * 设置返回值
     *
     * @param $return
     * @return $this
     */
    public function setReturn($return)
    {
        return $this->set('return', $return);
    }

    /**
     * 添加接口参数
     *
     * @param string $name 参数名
     * @param string $comment  注解
     * @param Enum({"string", "file", "json_array", "simple_array", "integer", "boolean", "text", 'url_param'}) $type 参数类型
     *    特别说明：
     *       url_param： 路由参数  形如 http://host/symfony-dev/web/app_dev.php/{url_param}
     * @param bool $is_null  是为为空
     * @param string $default  默认值
     * @param string $explain  说明
     * @param string $method 参数请求方式 不传则取接口设置的请求方式
     * @return $this
     */
    public function addParam($name, $comment = '', $type = 'string', $is_null = false, $default = '', $explain = '', $method = '')
    {
        if($is_null !== true && $is_null !== false){
            $this->Exception('param', $name, '设置项 is_null 可在 "true", "false" 中选择');
        }

        if(!in_array($type , array("string", "file", "json_array", "simple_array", "integer", "boolean", "text", "url_param"))){
            $this->Exception('param', $name, '设置项 type 可在 "file", "json_array", "simple_array", "integer", "boolean", "text", "url_param"  中选择');
        }

        if(!empty($method)){
            if(!in_array($method , array('get', 'post'))){
                $this->Exception('param', $method, '设置项可在 "get", "post" 中选择');
            }
        }

        self::$documents[self::$item]['param'][] = array(
            'name' => $name,
            'type' => $type,
            'is_null' => $is_null,
            'default' => $default,
            'comment' => $comment,
            'explain' => $explain,
            'is_null_string' => $is_null ? 'YES' : 'NO',
            'method' => $method
        );

        return $this;
    }

    public function addGetParam($name, $comment = '', $type = 'string', $is_null = false, $default = '', $explain = '')
    {
        return $this->addParam($name, $comment, $type, $is_null, $default, $explain, 'get');
    }


    public function addPostParam($name, $comment = '', $type = 'string', $is_null = false, $default = '', $explain = '')
    {
        return $this->addParam($name, $comment, $type, $is_null, $default, $explain, 'post');
    }
}