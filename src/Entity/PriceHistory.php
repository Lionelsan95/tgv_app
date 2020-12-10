<?php

namespace App\Entity;

use App\Repository\PriceHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PriceHistoryRepository::class)
 */
class PriceHistory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="priceHistories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="float")
     */
    private $oldPrice;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=Actions::class, inversedBy="priceHistories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $action;

    public function __construct(){
        $this->date = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOldPrice(): ?float
    {
        return $this->oldPrice;
    }

    public function setOldPrice(float $oldPrice): self
    {
        $this->oldPrice = $oldPrice;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function getStringDate(){
        return $this->date->format('d/m H:i');
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getAction(): ?Actions
    {
        return $this->action;
    }

    public function setAction(?Actions $action): self
    {
        $this->action = $action;

        return $this;
    }
}
