<?php

namespace App\Entity;

use App\Repository\LabelRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LabelRepository::class)
 * @ORM\Table(name="label", options={"comment":"标签表"})
 */
class Label
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", options={"comment":"标签名称"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="describe", type="string", options={"comment":"标签描述"})
     */
    private $describe;

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

    public function getDescribe(): ?string
    {
        return $this->describe;
    }

    public function setDescribe(string $describe): self
    {
        $this->describe = $describe;

        return $this;
    }

}
