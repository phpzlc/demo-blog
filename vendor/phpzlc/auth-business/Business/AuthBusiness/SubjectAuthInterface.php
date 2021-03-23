<?php
/**
 * 登录组件接口类
 *
 * Created by Trick
 * user: Trick
 * Date: 2020/12/21
 * Time: 10:14 上午
 */

namespace App\Business\AuthBusiness;

interface SubjectAuthInterface
{
    /**
     * 检查用户状态
     * 
     * @param $user
     * @return mixed
     */
    public function checkStatus($user);

    /**
     * 获取用户信息
     * 
     * @param $rules
     * @return mixed
     */
    public function user($rules);
}