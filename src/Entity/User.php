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
     * @ORM\Column(name="user_nickname", type="string", options={"comment":"用户昵称"})
     */
    private $userNickname;

    /**
     * @var string
     *
     * @ORM\Column(name="user_name", type="string", options={"comment":"用户名"})
     */
    private $userName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthday", type="datetime", options={"comment":"生日"})
     */
    private $birthday;

    /**
     * @var int
     *
     * @ORM\Column(name="age", type="integer", options={"comment":"年龄"})
     */
    private $age;

    /**
     * @var string
     *
     * @ORM\Column(name="face", type="string", nullable=true, options={"comment":"头像"})
     */
    private $face;

    /**
     * @var string
     *
     * @ORM\Column(name="mailbox", type="string", nullable=true, options={"comment":"邮箱"})
     */
    private $mailbox;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_disable", type="boolean", options={"comment":"是否禁用", "default":"0"})
     */
    private $isDisable;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_delete", type="boolean", options={"comment":"是否删除", "default":"0"})
     */
    private $isDelete;

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

    public function getUserNickname(): ?string
    {
        return $this->userNickname;
    }

    public function setUserNickname(string $userNickname): self
    {
        $this->userNickname = $userNickname;

        return $this;
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

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getFace(): ?string
    {
        return $this->face;
    }

    public function setFace(?string $face): self
    {
        $this->face = $face;

        return $this;
    }

    public function getMailbox(): ?string
    {
        return $this->mailbox;
    }

    public function setMailbox(?string $mailbox): self
    {
        $this->mailbox = $mailbox;

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

}
