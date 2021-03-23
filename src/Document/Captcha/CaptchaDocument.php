<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2021/3/10
 */

namespace App\Document\Captcha;


use PHPZlc\Document\Document;

class CaptchaDocument extends Document
{
    public function add()
    {
        $this->setGroup('图形验证码');
        return parent::add();
    }

    public function setUrl($url)
    {
        return parent::setUrl('/captcha'. $url);
    }

    public function generateAction()
    {
        $this->add()
            ->setTitle('生成图形验证码')
            ->setUrl('/generate')
            ->addParam('captcha', '业务类型', 'string', false, 'captcha', '根据业务中Api说明传参')
            ->addParam('format', '图形验证码格式', 'string', true,'', 'base64, 返回base64图形码')
            ->setReturn('image')
            ->generate();
    }
}