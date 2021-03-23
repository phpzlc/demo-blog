<?php
/**
 * 验证类
 *
 * User: Jay
 * Date: 2018/7/3
 */

namespace PHPZlc\Validate;

class Validate
{
    /**
     * 验证正则
     *
     * @param $variable
     * @param $regular
     * @return bool
     */
    public static function isRegular($variable, $regular)
    {
        if (!preg_match($regular, $variable)) {
            return false;
        }

        return true;
    }

    /**
     * 验证是否真实空
     *
     * 主要对empty() 对0的判断进行了取消
     *
     * @param $variable
     * @return bool
     */
    public static function isRealEmpty($variable)
    {
        if(empty($variable) && $variable !== 0 && $variable !== "0"){
            return true;
        }

        return false;
    }

    /**
     * 验证手机号码
     *
     * @param $variable
     * @return bool
     */
    public static function isMobile($variable)
    {
        return Validate::isRegular($variable, Regular::REG_MOBILE);
    }

    /**
     * 验证身份证号码
     *
     * @param $variable
     * @return bool
     */
    public static function isIdCard($variable)
    {
        return Validate::isRegular($variable, Regular::REG_ID_CARD_NO);
    }

    /**
     * 验证邮箱
     *
     * @param $variable
     * @return bool
     */
    public static function isEmail($variable)
    {
        return Validate::isRegular($variable, Regular::REG_EMAIL);
    }

    /**
     * 验证座机
     *
     * @param $variable
     * @return bool
     */
    public static function isTelephone($variable)
    {
        return Validate::isRegular($variable, Regular::REG_TELEPHONE);
    }

    /**
     * 验证密码格式
     *
     * [6-20位无特殊字符密码]
     *
     * @param $variable
     * @return bool
     */
    public static function isPassword($variable)
    {
        return Validate::isRegular($variable, Regular::REG_PASSWORD);
    }

    /**
     * 验证2位小数价格
     *
     * @param $variable
     * @return bool
     */
    public static function isPrice($variable)
    {
        return Validate::isRegular($variable, Regular::REG_PRICE);
    }

    /**
     * 判断小数
     *
     * @param $variable
     * @param int $min
     * @param int $max
     * @return bool
     */
    public static function isFloat($variable, $min = 0, $max = 999999)
    {
        $int_options = array(
            "options" => array(
                "min_range" => $min,
                "max_range" => $max
            )
        );

        return filter_var($variable, FILTER_VALIDATE_FLOAT, $int_options) !== false;
    }

    /**
     * 判断整数
     *
     * @param $number
     * @param int $min
     * @param int $max
     * @return mixed
     */
    public static function isInt($number, $min = 0, $max = 999999)
    {
        $int_options = array(
            "options" => array(
                "min_range" => $min,
                "max_range" => $max
            )
        );

        return filter_var($number, FILTER_VALIDATE_INT, $int_options) !== false;
    }


    /**
     * 判断QQ
     *
     * @param $variable
     * @return bool
     */
    public static function isQQ($variable)
    {
        return Validate::isRegular($variable, Regular::REG_QQ);
    }

    /**
     * 判断经度
     *
     * @param $variable
     * @return bool
     */
    public static function isLng($variable)
    {
        return Validate::isRegular($variable, Regular::REG_LNG);
    }

    /**
     * 判断维度
     *
     * @param $variable
     * @return mixed
     */
    public static function isLat($variable)
    {
        return Validate::isLat($variable);
    }

    /**
     * 判断日期
     *
     * @param $str
     * @param string $date_format Y-m-d H:i:s
     * @return bool
     */
    public static function isDate($str, $date_format = 'Y-m-d')
    {

        $unixTime = strtotime($str);
        if (!$unixTime) {
            return false;
        }

        if (date($date_format, $unixTime) != $str) {
            return false;
        }

        return true;
    }

    /**
     * 判断是否是闰年
     *
     * @param $year
     * @return bool
     */
    public static function isLeapYear($year)
    {
        return ((($year % 4) == 0) && ((($year % 100) != 0) || (($year % 400) == 0)));
    }


    /**
     * 判断网址
     *
     * @param $url
     * @return bool
     */
    public static function isUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * 判断ip
     *
     * @param $ip
     * @return bool
     */
    public static function isIp($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * 验证Mac
     *
     * @param $mac
     * @return bool
     */
    public static function isMac($mac)
    {
        return Validate::isRegular($mac, Regular::REG_MAC);
    }

    /**
     * 检查图片大小
     *
     * @param $resources
     * @param $width
     * @param $height
     * @return bool
     */
    public static function checkImageSize($resources, $width = 360, $height = 180)
    {
        if(!isset($_FILES[$resources])){
            return false;
        }

        $resources = $_FILES[$resources];

        $temp = $resources['tmp_name'];
        $img_info = getimagesize($temp);

        if($img_info['0'] != $width || $img_info['1'] != $height){
            return false;
        }

        return true;
    }

    /**
     *
     * 字符串是否超长
     *
     * @param $str
     * @param $max_len
     * @param string $encoding
     * @return bool
     */
    public static function isExtraLong($str, $max_len, $encoding = 'UTF-8')
    {
        if(mb_strlen($str, $encoding) > $max_len){
            return true;
        }

        return false;
    }
}