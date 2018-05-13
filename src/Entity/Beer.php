<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BeerRepository")
 */
class Beer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Brewery", inversedBy="beers")
     */
    private $brewery;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="beers")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Style", inversedBy="beers")
     */
    private $style;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     */
    private $ABV;

    /**
     * @ORM\Column(type="float")
     */
    private $IBU;

    /**
     * @ORM\Column(type="float")
     */
    private $SRM;

    /**
     * @ORM\Column(type="string", length=12)
     */
    private $UPC;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastModification;

    public function getId(): int
    {
        return $this->id;
    }

    public function getBrewery(): Brewery
    {
        return $this->brewery;
    }

    public function setBrewery(Brewery $brewery): self
    {
        $this->brewery = $brewery;
        return $this;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getStyle(): Style
    {
        return $this->style;
    }

    public function setStyle(Style $style): self
    {
        $this->style = $style;

        return $this;
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

    public function getABV(): ?float
    {
        return $this->ABV;
    }

    public function setABV(float $ABV): self
    {
        $this->ABV = $ABV;

        return $this;
    }

    public function getIBU(): ?float
    {
        return $this->IBU;
    }

    public function setIBU(float $IBU): self
    {
        $this->IBU = $IBU;

        return $this;
    }

    public function getSRM(): ?float
    {
        return $this->SRM;
    }

    public function setSRM(float $SRM): self
    {
        $this->SRM = $SRM;
        return $this;
    }

    public function getUPC(): ?string
    {
        return $this->UPC;
    }

    public function setUPC(string $UPC): self
    {
        $this->UPC = $UPC;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $Description): self
    {
        $this->description = $Description;

        return $this;
    }

    public function getLastModification(): ?\DateTimeInterface
    {
        return $this->lastModification;
    }

    public function setLastModification(\DateTimeInterface $LastModification): self
    {
        $this->lastModification = $LastModification;

        return $this;
    }
}
