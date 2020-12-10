<?php

namespace App\Entity;

use App\Repository\ActionsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ActionsRepository::class)
 */
class Actions
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $nom;

    /**
     * @ORM\Column(type="boolean")
     */
    private $bonus;

    /**
     * @ORM\Column(type="float")
     */
    private $prix;

    /**
     * @ORM\OneToMany(targetEntity=PriceHistory::class, mappedBy="action")
     */
    private $priceHistories;

    public function __construct()
    {
        $this->priceHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getBonus(): ?bool
    {
        return $this->bonus;
    }

    public function setBonus(bool $bonus): self
    {
        $this->bonus = $bonus;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    /**
     * @return Collection|PriceHistory[]
     */
    public function getPriceHistories(): Collection
    {
        return $this->priceHistories;
    }

    public function addPriceHistory(PriceHistory $priceHistory): self
    {
        if (!$this->priceHistories->contains($priceHistory)) {
            $this->priceHistories[] = $priceHistory;
            $priceHistory->setAction($this);
        }

        return $this;
    }

    public function removePriceHistory(PriceHistory $priceHistory): self
    {
        if ($this->priceHistories->removeElement($priceHistory)) {
            // set the owning side to null (unless already changed)
            if ($priceHistory->getAction() === $this) {
                $priceHistory->setAction(null);
            }
        }

        return $this;
    }
}
