<?php

namespace App\Entity;

use App\Business\AuthBusiness\UserAuthBusiness;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserAuthRepository;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\Validate\Validate;

/**
 * @ORM\Entity(repositoryClass=UserAuthRepository::class)
 * @ORM\Table(name="user_auth", options={"comment":"用户登录表"})
 *
 */
class UserAuth
{
    /**
     * @var string
     * @ORM\Id()
     * @ORM\Column(name="id", type="string")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="PHPZlc\PHPZlc\Doctrine\SortIdGenerator")
     *
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="subject_id", type="string", nullable=true, options={"comment":"主体ID"})
     */
    private $subjectId;

    /**
     * @var string
     *
     * @ORM\Column(name="subject_type", type="string", options={"comment":"主体类型"})
     */
    private $subjectType;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", options={"comment":"密码"})
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=4, options={"comment":"盐值"})
     */
    private $salt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_login_at", type="datetime", nullable=true, options={"comment":"最后登录时间"})
     */
    private $lastLoginAt;

    /**
     * @var string
     *
     * @ORM\Column(name="last_login_ip", type="string", nullable=true, options={"comment":"最后登录IP"})
     */
    private $lastLoginIp;

    /**
     * @var \DateTime
     *
     *
     * @ORM\Column(name="create_at", type="datetime", nullable=true, options={"comment":"创建时间"})
     */
    private $createAt;


    public function getId(): ?string
    {
        return $this->id;
    }

    public function getSubjectId(): ?string
    {
        return $this->subjectId;
    }

    public function setSubjectId(?string $subjectId): self
    {
        $this->subjectId = $subjectId;

        return $this;
    }

    public function getSubjectType(): ?string
    {
        return $this->subjectType;
    }

    public function setSubjectType(string $subjectType): self
    {
        $this->subjectType = $subjectType;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        if(!empty($password)){
            if(!Validate::isPassword($password)){
                Errors::setErrorMessage('请输入6-20位无特殊字符密码');
            }
        }

        $this->password = UserAuthBusiness::encryptPassword($password, $this->salt);

        return $this;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function setSalt(string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeInterface
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeInterface $lastLoginAt): self
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    public function getLastLoginIp(): ?string
    {
        return $this->lastLoginIp;
    }

    public function setLastLoginIp(?string $lastLoginIp): self
    {
        $this->lastLoginIp = $lastLoginIp;

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
}
