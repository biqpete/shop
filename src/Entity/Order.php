<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 * @ORM\Table(name="`order`")
 * @Serializer\ExclusionPolicy("all")
 */
class Order
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     * @SWG\Property(property="orderId", type="integer", example="7")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Serializer\Expose()
     * @Serializer\Groups({"api"})
     * @SWG\Property(property="cpu", type="string", example="i5")
     */
    private $cpu;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Serializer\Expose()
     * @Serializer\Groups({"api"})
     * @SWG\Property(property="ram", type="integer", example="16")
     */
    private $ram;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Serializer\Expose()
     * @Serializer\Groups({"api"})
     * @SWG\Property(property="hdd", type="integer", example="256")
     */
    private $hdd;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Serializer\Expose()
     * @Serializer\Groups({"api"})
     * @SWG\Property(property="screen", type="integer", example="15")
     */
    private $screen;

    /**
     * @ORM\Column(type="datetime")
     * @Serializer\Expose()
     * @Serializer\Groups({"api"})
     * @SWG\Property(property="createdAt", type="date", example="2018-09-11 11:40:36")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Expose()
     * @Serializer\Groups({"api"})
     * @SWG\Property(property="orderName", type="string", example="Order name")
     */
    private $orderName;

    /**
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     * @Serializer\Groups({"api"})
     * @SWG\Property(property="price", type="integer", example="1000")
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Expose()
     * @Serializer\Groups({"api"})
     * @SWG\Property(property="comment", type="string", example="Some comment")
     */
    private $comment;

    /**
     * @ORM\Column(type="boolean")
     * @Serializer\Expose()
     * @Serializer\Groups({"api"})
     * @SWG\Property(property="isSent", type="boolean", example="True")
     */
    private $isSent = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     * @SWG\Property(property="user", type="object", example="User")
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCpu(): ?string
    {
        return $this->cpu;
    }

    public function setCpu(?string $cpu): self
    {
        $this->cpu = $cpu;

        return $this;
    }

    public function getRam(): ?int
    {
        return $this->ram;
    }

    public function setRam(?int $ram): self
    {
        $this->ram = $ram;

        return $this;
    }

    public function getHdd(): ?int
    {
        return $this->hdd;
    }

    public function setHdd(?int $hdd): self
    {
        $this->hdd = $hdd;

        return $this;
    }

    public function getScreen(): ?int
    {
        return $this->screen;
    }

    public function setScreen(?int $screen): self
    {
        $this->screen = $screen;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getOrderName(): ?string
    {
        return $this->orderName;
    }

    public function setOrderName(string $orderName): self
    {
        $this->orderName = $orderName;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getIsSent(): ?bool
    {
        return $this->isSent;
    }

    public function setIsSent(bool $isSent): self
    {
        $this->isSent = $isSent;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
