<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/8/17
 */
namespace PHPZlc\Admin\Bundle\Controller;

use PHPZlc\Admin\Strategy\AdminStrategy;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;

class AdminController extends SystemBaseController
{
    /**
     * @var AdminStrategy
     */
    private $adminStrategy;

    /**
     * @var string
     */
    private $pageTag;
    
    public function inlet($returnType = SystemBaseController::RETURN_HIDE_RESOURCE, $isLogin = true)
    {
        $this->adminStrategy = new AdminStrategy($this->container);
        $this->adminStrategy->setPageTag($this->pageTag);
        
        return parent::inlet($returnType, $isLogin);
    }
}