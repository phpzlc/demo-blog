<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2020/8/26
 */

namespace PHPZlc\Admin\Strategy;

class Menu
{
    //链接打开新的窗口
    const URL_TARGET_BLANK = '_blank';

    //链接本页面窗口
    const URL_TARGET_SELF = '_self';

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $ico;

    /**
     * @var string
     */
    private $tag;

    /**
     * @var Menu[]
     */
    private $childs = [];

    /**
     * @var string
     */
    private $url = 'javascript:;';

    /**
     * @var string
     */
    private $url_target = self::URL_TARGET_SELF;

    /**
     * @var array
     */
    private $tags = [];

    /**
     * Menu constructor.
     * 
     * @param $title
     * @param null|string $ico
     * @param null|string $tag
     * @param null|string $url
     * @param null|string $url_target
     * @param Menu[] $childs
     */
    public function __construct($title, $ico = null, $tag = null, $url = null, $url_target = null, $childs = array())
    {
        $this->title = $title;

        if(!empty($this->ico)){
            $this->ico = $ico;
        }

        if($ico !== null){
            $this->ico = $ico;
        }

        if($tag !== null){
            $this->tag = $tag;
        }

        if($url !== null){
            $this->url = $url;
        }

        if($url_target != null){
            $this->url_target = $url_target;
        }

        $this->childs = $childs;
    }


    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getIco(): ?string
    {
        return $this->ico;
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @return Menu[]
     */
    public function getChilds(): array
    {
        return $this->childs;
    }

    /**
     * @param Menu|null $menus
     */
    public function setChilds($menus)
    {
        $this->childs = $menus;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        if(empty($this->tags)) {
            if(!empty($this->tag)){
                $this->tags[] = $this->tag;
            }
            
            foreach ($this->childs as $child){
                $this->tags = array_merge($this->tags, $child->getTags());
            }
        }

        return $this->tags;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getUrlTarget(): string
    {
        return $this->url_target;
    }
    
    
}