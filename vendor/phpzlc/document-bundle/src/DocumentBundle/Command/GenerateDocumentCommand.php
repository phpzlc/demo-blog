<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2018/5/2
 */

namespace PHPZlc\Document\DocumentBundle\Command;


use App\Document\Config;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Connection;
use MongoDB\Driver\Command;
use PHPZlc\Document\Document;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Matcher\RedirectableUrlMatcher;

class GenerateDocumentCommand extends Base
{
    /**
     * @var Connection|null
     */
    private $connection;

    public function __construct(Connection $connection = null)
    {
        parent::__construct();
        $this->connection = $connection;
    }

    private $vars = array(
        '$' => '$'
    );

    private $global = '';

    private $actions = '';

    private $group = '';

    private $config = array();

    public function configure()
    {
        $this
            ->setName($this->command_pre . 'generate:document')
            ->setDescription($this->description_pre . '生成API文档');
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title($this->getName());

        //TODO 全局配置数据
        $this->globalConfig();

        //TODO 接口数据
        foreach ($this->getDocumentClassArray($this->getRootPath() . '/src/Document') as $document) {
            $this->reader($document);
        }
        
        $this->actionsArrange();

        //TODO 代码生成

        //>1 目录资源重置
        exec('rm -rf ' . $this->rootApiDir());
        mkdir($this->rootApiDir());
        exec('cp -rf ' . __DIR__ . '/../Resources/Default/ApiDoc/* ' . $this->rootApiDir() . '/');

        //>2 生成静态页面
        $this->generateIndexFile();
        $this->generateBottomFile();
        $this->generateActionFile();
        $this->generateExplainFile();
        $this->generateDataFile();
        $this->generateDebugFile();

        //>3 生成打包件
        exec('cd ' . $this->rootApiDir() .'; zip -r ' . $this->rootApiDir() . '/' . $this->jsonToArray($this->global)['title'] . 'API文档.zip  .');

        $this->io->success('生成成功');

        return 0;
    }

    private function globalConfig()
    {
        $global['title'] = Config::getTitle();
        $global['publishing'] = Config::getPublishing();
        $global['domain'] = Config::getHost();
        $global['explain'] = Config::getExplain();
        $global['note'] = Config::getNote();
        $global['appendix'] = Config::getAppendix();

        foreach ($global as $key => $value){
            if(empty($value)){
                $global[$key] = '';
            }
        }

        $this->config['database_host'] = $this->connection->getHost();
        $this->config['database_name'] = $this->connection->getDatabase();
        $this->config['database_user_name'] = $this->connection->getUsername();
        $this->config['database_password'] = $this->connection->getPassword();

        if(empty($global['title'])) {
            $this->io->error('文档标题不能为空');
            exit;
        }

        if(empty($global['domain'])) {
            $this->io->error('根地址不能为空');
            exit;
        }

        $this->global = json_encode($global);
    }


    private function rootApiDir()
    {
        return $this->getRootPath() . '/public/apidoc';
    }

    /**
     * 得到文档类
     *
     * @param $dir_name
     * @return array
     */
    private function getDocumentClassArray($dir_name)
    {
        $arr = @scandir($dir_name);
        $return_array = [];
        if(!empty($arr)) {
            foreach ($arr as $value) {
                if(is_file($dir_name . '/' . $value) && strpos($value, 'Document') !== false){
                    $return_array[] = str_replace('/' ,'\\' , str_replace($this->getRootPath() . '/src/', '', 'App/'. $dir_name .'/'. rtrim($value, '.php')));
                }elseif(is_dir($dir_name . '/' . $value)){
                    if(!in_array($value, ['.', '..'])) {
                        $return_array = array_merge($return_array, $this->getDocumentClassArray($dir_name . '/' . $value));
                    }
                }
            }
        }

        return $return_array;
    }

    /**
     * 解析
     *
     * @param $document
     * @throws \ReflectionException
     */
    function reader($document)
    {
        $reflClass = new \ReflectionClass($document);

        $class = new $document();

        if($class instanceof Document){
            foreach ($reflClass->getMethods() as $action){
                if(strpos($action->getName(), 'Action') !== false && strpos($action->__toString(), str_replace('App/', '', str_replace('\\', '/', $document))) !== false){
                    $method = $action->getName();
                    $class->$method();
                }
            }
        }
    }

    /**
     * action数据加工整理 + 形成目录数组
     *
     * @param $actions
     */
    function actionsArrange()
    {
        $class = new Document();

        $actions = [];
        $group = [];
        $index = 0;

        foreach ($class->getDocuments() as $index => $value){
            if(isset($value['is_hide']) && $value['is_hide']){
                continue;
            }

            $value['ID'] = $index;

            if(empty($value['title'])){
                $this->io->error('报错Action:' . $value['physical_address']. '  原因:接口标题不能为空');
                exit;
            }
            if(empty($value['return_type'])){
                $this->io->error('报错Action:' . $value['physical_address']. '  原因:接口返回类型不能为空');
                exit;
            }
            if(empty($value['url'])){
                $this->io->error('报错Action:' . $value['physical_address']. '  原因:接口url不能为空');
                exit;
            }

            $value['original_title'] = $value['title'];
            $value['title'] = '[' . $value['return_type'] . '] ' . $value['title'];

            if(!empty($value['url']) && $value['is_host']) {
                $value['url'] = $this->jsonToArray($this->global)['domain'] . $value['url'];
            }

            if(empty($value['group'])){
                $group['默认分组'][] = $value;
                $value['group'] = '默认分组';
            }else{
                $item_group = explode('/' , $value['group']);
                $code = '$group';
                foreach ($item_group as $item){
                    if(!empty($item)) {
                        $code .= "['{$item}']";
                    }
                }
                $code .= '[] = $value;';
                eval($code);
            }

            $actions[$index] = $value;
        }

        $this->actions = json_encode($actions);
        $this->group = json_encode($group);
    }

    /**
     * json 转 数组
     *
     * @param $json
     * @return mixed
     */
    function jsonToArray($json)
    {
        return json_decode($json, true);
    }

    ////////////////////////////////////////////////生成html代码

    /**
     * 头部代码
     *
     * @param $type  1: 首页  2： 详情页面  3:action页面 4:debug页面
     * @param $other_head
     * @return string
     */
    function topHtml($type, $other_head = '')
    {
        $html = <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>{$this->jsonToArray($this->global)['title']}-APIDOC</title>
    <link rel="stylesheet" type="text/css" href="css/reset.css">
    <link rel="stylesheet" type="text/css" href="css/top.css">
    {$other_head}
EOF;

        switch ($type){
            case 1:
                $html .= <<<EOF
<link rel="stylesheet" type="text/css" href="css/index.css">    
EOF;
                break;
            case 2:
            case 3:
            case 4:
                $html .= <<<EOF
<link rel="stylesheet" type="text/css" href="css/info.css">  
<link rel="stylesheet" type="text/css" href="css/jquery.json-viewer.css">    
EOF;
                break;
        }

        $html .= <<<EOF
        
    <script src="js/jquery.js"></script>
</head>
<body>
    <div class="top">
        <a href="index.html"><div class="title"><img src="img/文档.png">{$this->jsonToArray($this->global)['title']}API文档</div></a>
        <div class="resources">
            <ul>
EOF;

        if($type != 4){
            $html .= <<<EOF
                <li><a href="explain.html">文档前要</a></li>
                <li><a href="data.html">数据字典</a></li>
EOF;
        }

        switch ($type){
            case 1:
                $html .= <<<EOF
                 <li><a href="{$this->jsonToArray($this->global)['title']}API文档.zip">打包ZIP</a></li>
EOF;
                break;
            case 3:
                $html .= <<<EOF
                <li><a id="Debug" style="color: #ff9900; cursor: pointer" >Debug</a></li>
EOF;
                break;
            case 2:
                break;
            case 4:
                $html .= <<<EOF
                <li><a href="" style="">Debug</a></li>
                <li><a href="data.html" id="return_action">返回接口</a></li>
EOF;
                break;
        }

        $html .= <<<EOF
        
            </ul>
        </div>
EOF;

        if($type == 1){
            $html .= <<<EOF
            
        <div class="search">
            <div class="search-input">
                <div class="search-img"></div>
                <input type="text" placeholder="请输入查找文字" id="search">
            </div>
        </div>
EOF;

        }

        $html .= <<<EOF
        
    </div>
EOF;

        return $html;


    }

    /**
     * 生成参数表格
     *
     * @param $title
     * @param $param
     * @param string $explain
     * @return string
     */
    function paramTable($title, $param, $explain = '', $type  = '')
    {
        $content = <<<EOF
        
        html = '<div class="content-center">';
        html += '<div class="son-title">{$title}：</div>';
        html += '<div class="table-param">';
EOF;
        if(!empty($explain)) {
            $content .= <<<EOF
         html += '<p class="explain">{$explain}</p>';   

EOF;
        }

        $content .= <<<EOF
        html += '<table  border="1px" width="100%">';
        html += '<thead>';
        html +=
            '<th style="width: 15%">参数名</th>' +
            '<th style="width: 15%">注解</th>' +
            '<th style="width: 6%">类型</th>' +
            '<th style="width: 8%;">允许为空</th>' +
            '<th style="width: 20%;">默认值</th>' +
            '<th>说明</th>';
        html += '</thead>';
        
        var is = false;
        $.each({$param}, function (k, v) {
            if(v.type == '{$type}'){
                v.method = 'asdasdas';
            }
            if(v.method == '{$type}' || v.type == '{$type}'){
                html += '<tr>';
                html +=
                    '<td>' + v.name + '</td>' +
                    '<td>' + v.comment + '</td>' +
                    '<td>' + v.type + '</td>' +
                    '<td>' + v.is_null_string + '</td>' +
                    '<td>' + v.default + '</td>' +
                    '<td>' + v.explain + '</td>';
                html += '</tr>';
                is = true;
            }
        });
        html += '</table></div></div>';
        
        if(is){
            $('.content').append(html);
        }
EOF;

        return $content;
    }

    /**
     * 生成首页文件
     */
    function generateIndexFile()
    {
        //生成 index.html
        $index_html = <<<EOF
{$this->topHtml(1)}
    <div class="content">
        <table>
            <tr>
                <td class="left" valign="top">
                    <div class="directory">
                        <ul class="content-son">
                        </ul>
                    </div>
                </td>
                <td class="right" valign="top">
                    <div class="list">
                        <ul class="content-son">
                        </ul>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <iframe src="bottom.html" width="100%" frameborder="0" style="display:block"></iframe>
    <script>
    
        $(function () {
            $('.directory .open').live('click', function () {
                $(this).parent().find('div').hide();
                $(this).parent().find('.open').addClass('close');
                $(this).parent().find('.open').removeClass('open');
                $(this).removeClass('open');
                $(this).addClass('close');
            });

            $('.directory .close').live('click', function () {
                $(this).parent().find('div').eq(0).show();
                $(this).removeClass('close');
                $(this).addClass('open');
            });
            
    
            $("#search").keydown(function(event) {  
                 if (event.keyCode == 13) { 
                     rightDirectory($(this).val());
                     leftDirectory($(this).val());
                 } 
             });  
            
             rightDirectory('');
             leftDirectory('');
        });

       
        // 生成右侧目录
        function rightDirectory(key_word) {
            var data = {$this->actions};
            var html = '';
            for (var i = 0; i < data.length; i++){
                var is = true;
                if(key_word != '' && key_word  != undefined){
                    if(data[i]['title'].toUpperCase().indexOf(key_word.toUpperCase()) == -1 && data[i]['group'].toUpperCase().indexOf(key_word.toUpperCase()) == -1){
                        is = false;
                    }
                }
                if(is){
                    html += '<li>';
                    html += '   <a href="action.html?ID='+ data[i]['ID'] +'">';
                    html += '       <img src="img/API.png">';
                    html += '       <label class="head-fond">'+ data[i]['title'] + '</label>'
                    html += '   </a>';
                    html += '</li>';
                }
            }
            $('.list .content-son').html(html);
        }
        
        //生成左侧html
        function leftDirectory(key_word){
            var data = {$this->group};
          
            if(key_word != '' && key_word  != undefined){
                data = leftDirectoryData(data, key_word);    
            }
            
            $('.directory .content-son').html(leftDirectoryHtml(data));
        }
        
        //左侧数据处理
        function leftDirectoryData(data, key_word) {
             var return_data = {};
             $.each(data ,function(key, value) {
                 if (isNaN(key)) {
                     if(key.toUpperCase().indexOf(key_word.toUpperCase()) != -1){
                         return_data[key] = value;
                     }
                     var r = leftDirectoryData(value, key_word);
                     if(count(r) > 0){
                         return_data[key] = r;
                     }
                } else {
                    if(value['title'].toUpperCase().indexOf(key_word.toUpperCase()) != -1){
                         return_data[key] = value;
                    }
                }
             });

            return return_data;
        }

        //数组总数
        function count(data)
        {
            var i = 0;
            $.each(data ,function(key, value) {
                i ++;
            });

            return i;
        }
        
        
        //生成左侧目录html
        function leftDirectoryHtml(data) {
            var html = '';
            $.each(data ,function(key, value) {
                if(isNaN(key) && value.length != 0){
                    html += '<li>';
                    html += '   <a class="open">';
                    html += '       <img src="img/文件夹.png">';
                    html += '       <label class="head-fond">'+ key +'</label>';
                    html += '   </a>';
                    html += '   <div class="item">';
                    html += '       <ul>';
                    html +=             leftDirectoryHtml(value);
                    html += '       </ul>';
                    html += '   </div>';
                    html += '</li>';
                }
                if(!isNaN(key)){
                    html += '<li>';
                    html += '   <a href="action.html?ID='+ value['ID'] +'">';
                    html += '       <img src="img/API.png">';
                    html += '       <label class="head-fond">'+ value['title'] + '</label>'
                    html += '   </a>';
                    html += '</li>';
                }
            });
            
            return html;
        }
       
        
    </script>
</body>
</html>
EOF;
        file_put_contents($this->rootApiDir() . '/index.html' , $index_html);

    }

    /**
     * 生成底部文件
     */
    function generateBottomFile()
    {
        $datetime = date('Y-m-d H:m:s');
        $count = count($this->jsonToArray($this->actions));
        $bottom_html = <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="css/reset.css">
    <link rel="stylesheet" type="text/css" href="css/bottom.css">
</head>
<body>
<div class="bottom">
    <p>最后生成时间：{$datetime}</p>
    <p>接口数量：{$count}</p>
</div>
</body>
</html>
EOF;
        file_put_contents($this->rootApiDir() . '/bottom.html' , $bottom_html);
    }

    /**
     * 生成Action文件
     */
    function generateActionFile()
    {
        $action_html = <<<EOF
{$this->topHtml(3)}
    <div class="content">
        <div class="content-center">
        </div>
    </div>
    <iframe src="bottom.html" width="100%" frameborder="0" style="display:block"></iframe>
    <script> 
        $(function () {
            var data = {$this->actions};
            var global = {$this->global};
            var id = getQueryString('ID');
            if(data[id] === undefined){
                $('.content-center').html('<div class="not-found">未找到接口</div>');
            }else {
                data = data[id];
                
                //显示接口名称, 组别, 说明
                var html =
                    '<div class="son-title">接口名称：</div>' +
                    '<div class="son-content">' + data.original_title + '</div>' +
                    '<div class="son-title">接口组别：</div>' +
                    '<div class="son-content">' + data.group + '</div>';

                if(data.explain !== null){
                    html +=
                        '<div class="son-title">接口说明：</div>' +
                        '<div class="son-content">' + data.explain + '</div>';
                }
                
                $('.content-center').html(html);
                 
                //显示接口地址, 传输模式,  返回类型
                 html =
                    '<div class="content-center">' +
                        '<div class="son-title">接口地址：</div>' +
                        '<div class="son-content">' + data.url + '</div>' +
                        '<div class="son-title">传输模式：</div>' +
                        '<div class="son-content">' + data.method + '</div>' +
                        '<div class="son-title">返回类型：</div>' +
                        '<div class="son-content">' + data.return_type + '</div>' +
                    '</div>';
                
                $('.content').append(html);
                    
                 
                //请求参数
                if(data.param.length != 0){
                    {$this->paramTable('URL型参数', 'data.param', '', 'url_param')}
                    {$this->paramTable('GET传输参数', 'data.param', '', 'get')}
                    {$this->paramTable('POST传输参数', 'data.param', '', 'post')}
                }
                 
                //请求返回值
                 if(data.return !== null){
                    html =
                        '<div class="content-center">' +
                        '<div class="son-title">接口返回值：</div>' +
                        '<div class="son-content">' + data.return + '</div>' +
                        '</div>';
                    $('.content').append(html);
                 }
                   
            }
            
            $('#Debug').click(function() {
                window.location.href = 'debug.html?ID='+id;
            });
        });
        
        
        function getQueryString(name)
        {
             var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
             var r = window.location.search.substr(1).match(reg);
             if(r!=null)return  unescape(r[2]); return null;
        }
       
    </script>
</body>
</html>
EOF;

        file_put_contents($this->rootApiDir() . '/action.html' , $action_html);
    }


    /**
     * 生成debugbug文件
     */
    function generateDebugFile()
    {
        $debug_html = <<<EOF
{$this->topHtml(4)}
    <div class="content">
        <div class="content-center">
           <div class="box-in">
                <span class="poster" id="method">POST</span>
                <input type="text" class="address-input" id="uri">
            </div>
            <input type="button" class="sends" value="Send">
            
            <from  action="" class="from-submit form" id="getfrom">
            <p class="from-title">get</p>
            </from>
            
            <form action="" class="from-submit form" id="postform">
                <p class="from-title">post</p>
            </form>
            
            <pre class="result-display-box" id="ajax-return-json"></pre>
            <div class="result-display-box" id="ajax-return-html" style="display:none"></div>
        </div>
    </div>
    <iframe src="bottom.html" width="100%" frameborder="0" style="display:block"></iframe>
    <script src="js/jquery.json-viewer.js"></script>
    <script>
     $(function(){
         var data = {$this->actions};
         var id = getQueryString('ID');
         $('#return_action').attr('href', 'action.html?ID=' + id);
         if(data[id] === undefined){
            $('.content-center').html('<div class="not-found">未找到接口</div>');
         } else {
            data = data[id] 
            $('#method').text(data['method'].toLocaleUpperCase());
            url = data['url'];
            
            var postform_html = '';
            var url_param = '';
            var getform_html = '';
             
             $.each(data.param, function (k, v) {
                 if(v.method == 'post' && v.type != 'url_param'){
                      postform_html += 
                          '<div class="form-center-box"> ' +
                          '    <ul>' +
                          '        <li class="keys">'+ v['name'] +'</li>' +
                          '            <li class="value">';
                     
                      if(v.type == 'file'){
                          postform_html += '            <input type="file" name="' + v['name'] + '" class="text-input">';
                      }else{
                          postform_html += '            <input type="text" name="' + v['name'] + '" value="'+ v['default'] +'" class="text-input">';
                      }
                      postform_html +=
                          '        </li>' +
                          '    </ul>' +
                          '</div>';
                  } else {
                     if(v.type == 'url_param'){
                         if(v.default == ''){
                             url += '/{url_param}'
                         }else{
                             url += '/' + v.default;
                         }
                     }else{
                       getform_html += 
                          '<div class="form-center-box"> ' +
                          '    <ul>' +
                          '        <li class="keys">'+ v['name'] +'</li>' +
                          '            <li class="value">' +
                          '            <input type="text" name="' + v['name'] + '" value="'+ v['default'] +'" class="text-input get_param">' +
                          '        </li>' +
                          '    </ul>' +
                          '</div>';
                     }
                  }
             });
             
             if(url_param != ''){
                url +=  '?' + url_param.substring(1);
             }
             
             $('#uri').val(url);
             $('#postform').append(postform_html);
             $('#getfrom').append(getform_html);
             
              $.each(data.param, function (k, v) {
                 if(v.method == 'post' && v.type != 'url_param' && v.default != ''){
                     $("#postform input[name='" + v.name + "']").val(v.default);
                 }
              });
         }
         
         //ajax方法
         $(".sends").click(function(){
             var uri = $('#uri').val();
             var url_param = '';
             
             $('.get_param').each(function() {
                    url_param += '&' + $(this).attr('name') + '=' +$(this).val();
             });
             
             if(url_param  != ''){
                 uri = uri + '?' + url_param.substr(1);
             }
             
             var data = $('#postform').serialize();             
             $.ajax({
              type: 'POST',
              cache: false,
              url: uri,
              data: data,
              success: function(response) {
                  $('#ajax-return-html').css('display',"none");
                  $('#ajax-return-json').css('display',"none");
                  
                  if(isJson(response)){
                      var json = JSON.stringify(response, null, 4);
                      $('#ajax-return-json').css('display',"block");
                      $('#ajax-return-json').jsonViewer(json, {collapsed:true});
                  } else {
                      $('#ajax-return-html').css('display',"block");
                      $('#ajax-return-html').html(response);
                  }
              },
              error: function(response, status, error) {
                  $('#ajax-return-html').css('display',"block");
                  $('#ajax-return-json').css('display',"none");
                  $('#ajax-return-html').html(response.responseText);
              }
            });
         });
         
         function getQueryString(name)
         {
             var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
             var r = window.location.search.substr(1).match(reg);
             if(r!=null)return  unescape(r[2]); return null;
         }
         
         function isJson(response) 
         {
            try {
              jQuery.parseJSON(response);  
              return true;  
            } catch (err) {
              return false;  
            }
         }

     })

    </script>
</body>
</html>
EOF;

        file_put_contents($this->rootApiDir() . '/debug.html', $debug_html);
    }

    /**
     * 生成全局说明文件
     */
    function generateExplainFile()
    {
        $explain_html = <<<EOF
{$this->topHtml(2)}
    <div class="content">
        <div class="content-center">
        </div>
    </div>
    <iframe src="bottom.html" width="100%" frameborder="0" style="display:block"></iframe>
    <script> 
        $(function () {
            
            var global = {$this->global};
            
            //文档标题  文档出版商  接口根域名 说明  
            var html =   
                '<div class="son-title">标题：</div>' +
                '<div class="son-content">' + global.title + '</div>';
                
            if(global.publishing !== ''){
                 html +=
                      '<div class="son-title">出版商：</div>' +
                      '<div class="son-content">' + global.publishing + '</div>';
            }
            
            if(global.domain !== ''){
                 html +=
                      '<div class="son-title">根域名：</div>' +
                      '<div class="son-content">' + global.domain + '</div>';
            }
            
            if(global.explain !== ''){
                html +=
                    '<div class="son-title">接口说明：</div>' +
                    '<div class="son-content">' + global.explain + '</div>';
            }
            
            $('.content-center').html(html);
                
         
            //注意
            if(global.note !== ''){
                html =
                    '<div class="content-center">' +
                    '<div class="son-title">注意：</div>' +
                    '<div class="son-content">' + global.note + '</div>' +
                    '</div>';
                $('.content').append(html); 
            }      
           
            //附录
             if(global.appendix !== ''){
                html =
                    '<div class="content-center">' +
                    '<div class="son-title">附录：</div>' +
                    '<div class="son-content">' + global.appendix + '</div>' +
                    '</div>';
                 $('.content').append(html);          
             }   
            
        });
             
    </script>
</body>
</html>
EOF;

        file_put_contents($this->rootApiDir() . '/explain.html' , $explain_html);
    }

    /**
     * 生成数据字典
     */
    function generateDataFile()
    {
        $data_html = <<<EOF
            {$this->topHtml(2, '
   <style>
        .warp{margin:auto; width:1000px;}
        .warp h3{margin:0px; padding:0px; line-height:30px; margin-top:10px;}
        .c1 { width: 120px; }
        .c2 { width: 150px; }
        .c3 { width: 150px; }
        .c4 { width: 80px; text-align:center;}
        .c5 { width: 80px; text-align:center;}
        .c6 { width: 270px; }
    </style>'
        )}
            <div class="content">
EOF;

        $config = $this->config;

        if (empty($config['database_name']) || empty($config['database_user_name']) || empty($config['database_password']) || empty($config['database_host'])) {
            $data_html .= '<h1>数据库配置不能为空</h1>';
        } else {
            //配置数据库
            $database = $config['database_name'];
            $dbserver = $config['database_host'];
            $dbusername = $config['database_user_name'];
            $dbpassword = $config['database_password'];
            $mysql_conn = mysqli_connect("$dbserver", "$dbusername", "$dbpassword");
            if (!$mysql_conn) {
                $data_html .= '<div class="not-found">数据库连接失败</div>';
            } else {
                if (!mysqli_select_db($mysql_conn, $database)) {
                    $data_html .= '<div class="not-found">Database ' . $database . ' 未找到</div>';
                } else {
                    mysqli_query($mysql_conn, 'SET NAMES utf8');
                    $table_result = mysqli_query($mysql_conn, 'show tables');

                    $no_show_table = array();    //不需要显示的表
                    $no_show_field = array();   //不需要显示的字段

                    //取得所有的表名
                    while ($row = mysqli_fetch_array($table_result)) {
                        if (!in_array($row[0], $no_show_table)) {
                            $tables[]['TABLE_NAME'] = $row[0];
                        }
                    }

                    //循环取得所有表的备注及表中列消息
                    foreach ($tables as $k => $v) {
                        $sql = 'SELECT * FROM ';
                        $sql .= 'INFORMATION_SCHEMA.TABLES ';
                        $sql .= 'WHERE ';
                        $sql .= "table_name = '{$v['TABLE_NAME']}'  AND table_schema = '{$database}'";
                        $table_result = mysqli_query($mysql_conn, $sql);
                        while ($t = mysqli_fetch_array($table_result)) {
                            $tables[$k]['TABLE_COMMENT'] = $t['TABLE_COMMENT'];
                        }

                        $sql = 'SELECT * FROM ';
                        $sql .= 'INFORMATION_SCHEMA.COLUMNS ';
                        $sql .= 'WHERE ';
                        $sql .= "table_name = '{$v['TABLE_NAME']}' AND table_schema = '{$database}'";

                        $fields = array();
                        $field_result = mysqli_query($mysql_conn, $sql);
                        while ($t = mysqli_fetch_array($field_result)) {
                            $fields[] = $t;
                        }
                        $tables[$k]['COLUMN'] = $fields;
                    }

                    mysqli_close($mysql_conn);

                    $html = '';
                    //循环所有表
                    foreach ($tables as $k => $v) {
                        $html .= '	<h3>' . ($k + 1) . '、' . $v['TABLE_NAME'] . ($v['TABLE_COMMENT'] ? '（' . $v['TABLE_COMMENT'] . '）' : '') . '  </h3>' . "\n";
                        $html .= '	<table border="1" cellspacing="0" cellpadding="0" width="100%">' . "\n";
                        $html .= '		<tbody>' . "\n";
                        $html .= '			<tr>' . "\n";
                        $html .= '				<th class="c1">字段名</th>' . "\n";
                        $html .= '				<th class="c2">数据类型</th>' . "\n";
                        $html .= '				<th class="c3">默认值</th>' . "\n";
                        $html .= '				<th class="c4">允许非空</th>' . "\n";
                        $html .= '				<th class="c5">索引类型</th>' . "\n";
                        $html .= '				<th class="">备注</th>' . "\n";
                        $html .= '			</tr>' . "\n";

                        foreach ($v['COLUMN'] as $f) {
                            if (!isset($no_show_field[$v['TABLE_NAME']]) || !is_array($no_show_field[$v['TABLE_NAME']])) {
                                $no_show_field[$v['TABLE_NAME']] = array();
                            }
                            if (!in_array($f['COLUMN_NAME'], $no_show_field[$v['TABLE_NAME']])) {
                                $html .= '			<tr>' . "\n";
                                $html .= '				<td>' . $f['COLUMN_NAME'] . '</td>' . "\n";
                                $html .= '				<td>' . $f['COLUMN_TYPE'] . '</td>' . "\n";
                                $html .= '				<td>' . $f['COLUMN_DEFAULT'] . '</td>' . "\n";
                                $html .= '				<td>' . $f['IS_NULLABLE'] . '</td>' . "\n";
                                $html .= '				<td>' . ($f['EXTRA'] ? $f['EXTRA'] : '&nbsp;') . '</td>' . "\n";
                                $html .= '				<td>' . $f['COLUMN_COMMENT'] . '</td>' . "\n";
                                $html .= '			</tr>' . "\n";
                            }
                        }
                        $html .= '		</tbody>' . "\n";
                        $html .= '	</table>' . "\n";
                    }

                    $data_html .= <<<EOF
    <div class="warp">                
        <h1 style="text-align:center;">数据库{$database}数据字典</h1>
        {$html}
    </div>    
EOF;

                }
            }
        }

        $data_html .= <<<EOF
</div>
</body>
</html>
EOF;

        file_put_contents($this->rootApiDir() . '/data.html', $data_html);
    }
}

