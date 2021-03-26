<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\ORM\Mapping as ORM;

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
     * @var Label
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Label")
     * @ORM\JoinColumn(name="label_id", referencedColumnName="id")
     */
    private $label;

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
     * @ORM\Column(name="thumbnail", type="string", options={"comment":"文章缩略图"})
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

    public function getId(): ?string
    {
        return $this->id;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

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

    public function getLabel(): ?Label
    {
        return $this->label;
    }

    public function setLabel(?Label $label): self
    {
        $this->label = $label;

        return $this;
    }
}
