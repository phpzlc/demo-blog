<?php

namespace App\Entity;

use App\Repository\SortRepository;
use Doctrine\ORM\Mapping as ORM;
use PHPZlc\PHPZlc\Doctrine\ORM\Mapping\OuterColumn;

/**
 * @ORM\Entity(repositoryClass=SortRepository::class)
 * @ORM\Table(name="sort", options={"comment":"分类表"})
 */
class Sort
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="string")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="PHPZlc\PHPZlc\Doctrine\SortIdGenerator")
     */
    private $id;

    /**
     * @var Sort
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Sort")
     * @ORM\JoinColumn(name="parent_sort_id", referencedColumnName="id")
     */
    private $parentSort;

    /**
     * @var string
     *
     * @ORM\Column(name="sort_no", type="string", options={"comment":"分类编号"})
     */
    private $sortNo;

    /**
     * @var string
     *
     * @ORM\Column(name="sort_name", type="string", options={"comment":"分类名称"})
     */
    private $sortName;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_del", type="boolean", options={"comment":"是否删除", "default":0})
     */
    private $isDel = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_disable", type="boolean", options={"comment":"是否禁用", "default":0})
     */
    private $isDisable = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_at", type="datetime", options={"comment":"创建时间"})
     */
    private $createAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_at", type="datetime", nullable=true, options={"comment":"更新时间"})
     */
    private $updateAt;

    /**
     * @OuterColumn(name="articles_numbers", type="string", sql="(SELECT COUNT(a.id) FROM article a WHERE a.sort_id = sql_pre.id)")
     */
    public $articlesNumbers;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getSortNo(): ?string
    {
        return $this->sortNo;
    }

    public function setSortNo(string $sortNo): self
    {
        $this->sortNo = $sortNo;

        return $this;
    }

    public function getSortName(): ?string
    {
        return $this->sortName;
    }

    public function setSortName(string $sortName): self
    {
        $this->sortName = $sortName;

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

    public function getIsDisable(): ?bool
    {
        return $this->isDisable;
    }

    public function setIsDisable(bool $isDisable): self
    {
        $this->isDisable = $isDisable;

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

    public function getParentSort(): ?self
    {
        return $this->parentSort;
    }

    public function setParentSort(?self $parentSort): self
    {
        $this->parentSort = $parentSort;

        return $this;
    }

    public function getArticlesNumbers(): ?string
    {
        return $this->articlesNumbers;
    }

}
