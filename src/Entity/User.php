<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`", options={"comment":"用户表"})
 */
class User
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
     * @var string
     *
     * @ORM\Column(name="user_name", type="string", options={"comment":"用户名"})
     */
    private $userName;

    /**
     * @var UserAuth
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\UserAuth")
     * @ORM\JoinColumn(name="user_auth_id", referencedColumnName="id")
     */
    private $userAuth;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_disable", type="boolean", options={"comment":"是否禁用", "default":0})
     */
    private $isDisable = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_delete", type="boolean", options={"comment":"是否删除", "default":0})
     */
    private $isDelete = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_at", type="datetime", options={"comment":"注册时间"})
     */
    private $createAt;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): self
    {
        $this->userName = $userName;

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

    public function getIsDisable(): ?bool
    {
        return $this->isDisable;
    }

    public function setIsDisable(bool $isDisable): self
    {
        $this->isDisable = $isDisable;

        return $this;
    }

    public function getIsDelete(): ?bool
    {
        return $this->isDelete;
    }

    public function setIsDelete(bool $isDelete): self
    {
        $this->isDelete = $isDelete;

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
