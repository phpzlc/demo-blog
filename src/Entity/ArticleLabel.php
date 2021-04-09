<?php

namespace App\Entity;

use App\Repository\ArticleLabelRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ArticleLabelRepository::class)
 * @ORM\Table(name="article_label", options={"博客文章标签中间表"})
 */
class ArticleLabel
{
    /**
     * @var string
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="string")
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
     * @var Label
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Label")
     * @ORM\JoinColumn(name="label_id", referencedColumnName="id")
     */
    private $label;

    public function getId(): ?string
    {
        return $this->id;
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
