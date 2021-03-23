<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/11/5
 */

namespace PHPZlc\Admin\Strategy;


class TopMenu
{
    private $html;
    
    private $tag;

    private $function;

    private $vueData;
    
    public function __construct($html, $tag = null, $vueData = null, $function = null)
    {
        $this->html = $html;
        $this->tag = $tag;
        $this->vueData = $vueData;
        $this->function = $function;
    }

    /**
     * @return mixed
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @return null
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @return mixed|null
     */
    public function getVueData()
    {
        return $this->vueData;
    }

    /**
     * @return mixed|null
     */
    public function getFunction()
    {
        return $this->function;
    }

}