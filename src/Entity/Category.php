<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastModification;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Beer", mappedBy="category")
     */
    private $beers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Style", mappedBy="category")
     */
    private $styles;

    public function __construct()
    {
        $this->beers = new ArrayCollection();
        $this->styles = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLastModification(): ?\DateTimeInterface
    {
        return $this->lastModification;
    }

    public function setLastModification(?\DateTimeInterface $lastModification): self
    {
        $this->lastModification = $lastModification;

        return $this;
    }

    public function getBeers(): ArrayCollection
    {
        return $this->beers;
    }

    public function addBeer(Beer $beer): self
    {
        if (!$this->beers->contains($beer)) {
            $this->beers[] = $beer;
            $beer->setCategory($this);
        }

        return $this;
    }

    public function removeBeer(Beer $beer): self
    {
        if ($this->beers->contains($beer)) {
            $this->beers->removeElement($beer);
            if ($beer->getCategory() === $this) {
                $beer->setCategory(null);
            }
        }

        return $this;
    }

    public function addStyle(Style $style): self
    {
        if (!$this->styles->contains($style)) {
            $this->styles[] = $style;
            $style->setCategory($this);
        }

        return $this;
    }

    public function removeStyle(Style $style): self
    {
        if ($this->styles->contains($style)) {
            $this->beers->removeElement($style);
            if ($style->getCategory() === $this) {
                $style->setCategory(null);
            }
        }

        return $this;
    }
}
