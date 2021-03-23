<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2021/3/9
 */

namespace App\Controller\Captcha;

use App\Business\CaptchaBusiness\CaptchaBusiness;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptchaController extends AbstractController
{
    /**
     * 图形验证码
     *
     * @param Request $request
     * @return Response
     */
    public function generate(Request $request)
    {
        return (new CaptchaBusiness($this->container))->captcha($request->get('captcha', 'captcha'), $request->get('format'));
    }
}