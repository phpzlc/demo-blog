<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/9/28
 */

namespace App\Business\CaptchaBusiness;


use Gregwar\Captcha\CaptchaBuilder;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Business\AbstractBusiness;
use PHPZlc\PHPZlc\Bundle\Service\Log\Log;
use Symfony\Component\HttpFoundation\Response;

class CaptchaBusiness extends AbstractBusiness
{
    /**
     * 图形验证码
     *
     * @param $captcha
     * @param $format
     */
    public function captcha($captcha, $format = null)
    {
        $builder = new CaptchaBuilder();
        $builder
            ->setIgnoreAllEffects(true)
            ->build();

        $this->get('session')->set($captcha, $builder->getPhrase());

        if($format == 'base64') {
            return new Response($builder->inline()); //返回base64图形码
        }

        header('Content-type: image/jpeg');
        header('Pragma: no-cache');
        header('Cache-Control: no-cache');

        $builder->output();

        return new Response();
    }

    /**
     * 验证图形验证码是否正确
     *
     * @param $captcha
     * @param $salt
     * @param string $error_string
     * @return bool
     */
    public function isCaptcha($captcha, $salt, $error_string = '图形验证码')
    {
        if(empty($salt)){
            Errors::setErrorMessage($error_string . '不能为空');
        } else if(strtolower($this->get('session')->get($captcha)) != strtolower($salt)){
            Errors::setErrorMessage($error_string . '不正确');
        }

        $this->get('session')->remove($captcha);

        return !Errors::isExistError();
    }
}