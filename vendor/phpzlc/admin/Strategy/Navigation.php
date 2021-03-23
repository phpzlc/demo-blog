<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/8/26
 */

namespace PHPZlc\Admin\Strategy;


class Navigation
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $url;

    public function __construct($title, $url = '')
    {
        $this->title = $title;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}