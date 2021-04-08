<?php
/**
 * 富文本编辑框内容处理
 *
 * Created by Trick
 * user: Trick
 * Date: 2021/3/9
 * Time: 10:14 上午
 */

namespace App\Tools;

class RichText
{
    public static function richTextAbsoluteUrl($html_content, $host)
    {
        if (preg_match_all("/(<img[^>]+src=\"([^\"]+)\"[^>]*>)|(<a[^>]+href=\"([^\"]+)\"[^>]*>)|(<img[^>]+src='([^']+)'[^>]*>)|(<a[^>]+href='([^']+)'[^>]*>)/i", $html_content, $regs)) {
            foreach ($regs [0] as $num => $url) {
                $html_content = str_replace($url, self::lIIIIl($url, $host), $html_content);
            }
        }

        return $html_content;
    }

    public static function lIIIIl($l1, $l2)
    {
        if (preg_match("/(.*)(href|src)\=(.+?)( |\/\>|\>).*/i", $l1, $regs)) {
            $I2 = $regs [3];
        }
        if (strlen($I2) > 0) {
            $I1 = str_replace(chr(34), "", $I2);
            $I1 = str_replace(chr(39), "", $I1);
        } else {
            return $l1;
        }
        $url_parsed = parse_url($l2);
        $scheme = isset($url_parsed['scheme']) ? $url_parsed ["scheme"] : '';
        if ($scheme != "") {
            $scheme = $scheme . "://";
        }
        $host = isset($url_parsed ["host"]) ? $url_parsed['host'] : '';
        $l3 = $scheme . $host;
        if (strlen($l3) == 0) {
            return $l1;
        }

//        $path = isset($url_parsed ["path"]) ? dirname($url_parsed ["path"]) : '' ;
        $path = $url_parsed["path"];
        if(!empty($path)){
            if ($path [0] == "\\") {
                $path = "";
            }
        }
        $pos = strpos($I1, "#");
        if ($pos > 0)
            $I1 = substr($I1, 0, $pos);

        //判断类型
        if (preg_match("/^(http|https|ftp):(\/\/|\\\\)(([\w\/\\\+\-~`@:%])+\.)+([\w\/\\\.\=\?\+\-~`@\':!%#]|(&amp;)|&)+/i", $I1)) {
            return $l1;
        } //http开头的url类型要跳过
        elseif ($I1 [0] == "/") {
            $I1 = $l3 . $I1;
        } //绝对路径
        elseif (substr($I1, 0, 3) == "../") { //相对路径
            while (substr($I1, 0, 3) == "../") {
                $I1 = substr($I1, strlen($I1) - (strlen($I1) - 3), strlen($I1) - 3);
                if (strlen($path) > 0) {
                    $path = dirname($path);
                }
            }
            $I1 = $l3 . $path . "/" . $I1;
        } elseif (substr($I1, 0, 2) == "./") {
            $I1 = $l3 . $path . substr($I1, strlen($I1) - (strlen($I1) - 1), strlen($I1) - 1);
        } elseif (strtolower(substr($I1, 0, 7)) == "mailto:" || strtolower(substr($I1, 0, 11)) == "javascript:") {
            return $l1;
        } else {
            $I1 = $l3 . $path . "/" . $I1;
        }
        return str_replace($I2, "\"$I1\"", $l1);
    }
    /**
     *  富文本绝对路径替换成相对路径
     * @param $html_content
     * @param $host
     * @return mixed
     */
    public static function richTextRelativeUrl($html_content, $host)
    {
        return str_replace($host, '', $html_content);
    }
}