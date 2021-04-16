<?php

namespace App\Entity;

use App\Repository\CollectionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CollectionRepository::class)
 * @ORM\Table(name="collection", options={"comment":"收藏表"})
 */
class Collection
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
     * @var Article
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Article")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    private $article;

    /**
     * @var UserAuth
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\UserAuth")
     * @ORM\JoinColumn(name="user_auth_id", referencedColumnName="id")
     */
    private $userAuth;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_at", type="datetime", options={"comment":"收藏时间"})
     */
    private $createAt;

    public function getId(): ?string
    {
        return $this->id;
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

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

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

}
