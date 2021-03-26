<?php

namespace App\Entity;

use App\Repository\CommentaryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommentaryRepository::class)
 * @ORM\Table(name="commentary", options={"comment":"评论表"})
 */
class Commentary
{
    /**
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="string")
     * @ORM\Id
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
    private $user;

    /**
     * @var Article
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Article")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    private $article;

    /**
     * @var Commentary
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Commentary")
     * @ORM\JoinColumn(name="parent_commentary_id", referencedColumnName="id")
     */
    private $parentCommentary;

    /**
     * @ORM\Column(name="content", type="text", options={"comment":"评论内容"})
     */
    private $content;

    /**
     * @var integer
     *
     * @ORM\Column(name="likes", type="integer", nullable=true, options={"comment":"评论点赞数"})
     */
    private $likes;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creat_at", type="datetime", options={"comment":"评论时间"})
     */
    private $creatAt;

    public function getId(): ?string
    {
        return $this->id;
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

    public function getCreatAt(): ?\DateTimeInterface
    {
        return $this->creatAt;
    }

    public function setCreatAt(\DateTimeInterface $creatAt): self
    {
        $this->creatAt = $creatAt;

        return $this;
    }

    public function getUser(): ?UserAuth
    {
        return $this->user;
    }

    public function setUser(?UserAuth $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function getParentCommentary(): ?self
    {
        return $this->parentCommentary;
    }

    public function setParentCommentary(?self $parentCommentary): self
    {
        $this->parentCommentary = $parentCommentary;

        return $this;
    }
}
