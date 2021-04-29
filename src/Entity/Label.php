<?php

namespace App\Entity;

use App\Repository\LabelRepository;
use Doctrine\ORM\Mapping as ORM;
use PHPZlc\PHPZlc\Doctrine\ORM\Mapping\OuterColumn;

/**
 * @ORM\Entity(repositoryClass=LabelRepository::class)
 * @ORM\Table(name="label", options={"comment":"标签表"})
 */
class Label
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", options={"comment":"标签名称"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="illustrate", type="string", options={"comment":"标签描述"})
     */
    private $illustrate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_at", type="datetime", options={"comment":"创建时间"})
     */
    private $createAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_at", type="datetime", nullable=true, options={"comment":"编辑时间"})
     */
    private $updateAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_del", type="boolean", options={"comment":"是否删除", "default":0})
     */
    private $isDel = false;

    /**
     *
     * @OuterColumn(name="article_numbers", type="string", options={"comment":"外接字段,查询标签下有多少文章"}, sql=" (SELECT COUNT(al.id) FROM article_label al WHERE al.label_id = sql_pre.id)")
     */
    private $articleNumbers;


    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getIsDel(): ?bool
    {
        return $this->isDel;
    }

    public function setIsDel(bool $isDel): self
    {
        $this->isDel = $isDel;

        return $this;
    }

    public function getIllustrate(): ?string
    {
        return $this->illustrate;
    }

    public function setIllustrate(string $illustrate): self
    {
        $this->illustrate = $illustrate;

        return $this;
    }

    public function getArticleNumbers(): ?string
    {
        return $this->articleNumbers;
    }

}
