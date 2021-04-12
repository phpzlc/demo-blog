<?php

namespace App\Entity;

use App\Business\UploadBusiness\UploadFile;
use App\Repository\ArticleRepository;
use App\Safety\ActionLoad;
use App\Tools\RichText;
use Doctrine\ORM\Mapping as ORM;
use PHPZlc\PHPZlc\Doctrine\ORM\Mapping\OuterColumn;

/**
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 * @ORM\Table(name="article", options={"comment":"文章表"})
 */
class Article
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="string")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="PHPZlc\PHPZlc\Doctrine\SortIdGenerator")
     */
    private $id;

    /**
     * @var UserAuth
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\UserAuth")
     * @ORM\JoinColumn(name="user_auth_id", referencedColumnName="id")
     */
    private $userAuth;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", options={"comment":"文章标题"})
     */
    private $title;

    /**
     * @ORM\Column(name="content", type="text", options={"comment":"文章内容"})
     */
    private $content;

    /**
     * @var integer
     *
     * @ORM\Column(name="likes", type="integer", nullable=true, options={"comment":"点赞量"})
     */
    private $likes;

    /**
     * @var integer
     *
     * @ORM\Column(name="views", type="integer", nullable=true, options={"comment":"浏览量"})
     */
    private $views;

    /**
     * @var string
     *
     * @ORM\Column(name="thumbnail", type="string", nullable=true, options={"comment":"文章缩略图"})
     */
    private $thumbnail;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_at", type="datetime",options={"comment":"发布时间"})
     */
    private $createAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_at", type="datetime", nullable=true, options={"comment":"修改时间"})
     */
    private $updateAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_del", type="boolean", options={"comment":"是否删除", "default":0})
     */
    private $isDel = false;

    /**
     * @var string
     *
     * @OuterColumn(name="labels", type="simple_array", sql = "SELECT GROUP_CONCAT(l.name) FROM label l WHERE l.id in (SELECT label_id FROM article_label al WHERE al.article_id = sql_pre.id )")
     */
    public $labels;

    public function getId(): ?string
    {
        return $this->id;
    }


    /**
     * 将富文本内容相对地址转换成前端显示的绝对地址
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        $request = ActionLoad::$globalContainer->get('request_stack');
        $host = $request->getCurrentRequest()->getSchemeAndHttpHost(). $request->getCurrentRequest()->getBasePath();

        return RichText::richTextAbsoluteUrl($this->content, $host);
    }

    /**
     * 将富文本内容的绝地地址转换成数据库存储的相对位置
     *
     * @param string $content
     * @return $this
     */
    public function setContent(string $content): self
    {
        $request = ActionLoad::$globalContainer->get('request_stack');
        $host = $request->getCurrentRequest()->getSchemeAndHttpHost(). $request->getCurrentRequest()->getBasePath() . '/';

        $this->content = RichText::richTextRelativeUrl($content, $host);

        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(?int $likes): self
    {
        $this->likes = $likes;

        return $this;
    }

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function setViews(?int $views): self
    {
        $this->views = $views;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function getThumbnailPath()
    {
        return UploadFile::getFileNetworkPath(ActionLoad::$globalContainer, $this->getThumbnail());
    }

    public function setThumbnail(string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeInterface $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(?\DateTimeInterface $updateAt): self
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function getUserAuth(): ?UserAuth
    {
        return $this->userAuth;
    }

    public function setUserAuth(?UserAuth $userAuth): self
    {
        $this->userAuth = $userAuth;

        return $this;
    }

    public function getIsDel(): ?bool
    {
        return $this->isDel;
    }

    public function setIsDel(bool $isDel): self
    {
        $this->isDel = $isDel;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getLabels(): ?array
    {
        return $this->labels;
    }

}
