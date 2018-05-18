<?php

namespace App\Util;

use App\Entity\Beer;
use App\Entity\Geocode;
use App\Entity\Style;

class BeerCollector
{
    private $potentialLocations;
    private $collectedBeers;
    private $collectedStyles;

    public function __construct()
    {
        $this->collectedBeers = [];
        $this->collectedStyles = [];
        $this->potentialLocations = [];
    }

    public function getCollectedBeerNames(bool $sort = false): array
    {
        $beerNames = [];
        foreach ($this->collectedBeers as $beer) {
            $beerNames[] = $beer->getName();
            if ($sort) {
                sort($beerNames);
            }
        }
        return $beerNames;
    }

    public function getCollectedStyleNames(bool $sort = false): array
    {
        $styleNames = [];
        foreach ($this->collectedStyles as $style) {
            $styleNames[] = $style->getName();
            if ($sort) {
                sort($styleNames);
            }
        }
        return $styleNames;
    }

    public function addBeers(Geocode $location): void
    {
        $beers = $location->getBrewery()->getBeers();
        $this->potentialLocations[$location->getId()] = [];
        foreach ($beers as $beer) {
            if (!$this->beerCollected($beer)) {
                $this->potentialLocations[$location->getId()][] = $beer;
            }
        }
    }

    private function beerCollected(Beer $beer): bool
    {
        foreach ($this->collectedBeers as $collectedBeer) {
            if ($collectedBeer->getName() === $beer->getName()) {
                return true;
            }
        }
        return false;
    }

    public function collectFrom(Geocode $location): void
    {
        $beers = $location->getBrewery()->getBeers();
        foreach ($beers as $beer) {
            if (!$this->beerCollected($beer)) {
                $this->collectedBeers[] = $beer;
            }
            if ($beer->hasStyle() && !$this->styleCollected($beer->getStyle())) {
                $this->collectedStyles[] = $beer->getStyle();
            }
        }
    }

    private function styleCollected(Style $style): bool
    {
        foreach ($this->collectedStyles as $collectedStyle) {
            if ($collectedStyle->getName() === $style->getName()) {
                return true;
            }
        }
        return false;
    }

    public function collectMost()
    {
        $locationId = $this->findIdWithMostBeers();
        foreach ($this->potentialLocations[$locationId] as $beer) {
            $this->collectedBeers[] = $beer;
            if ($beer->hasStyle() && !$this->styleCollected($beer->getStyle())) {
                $this->collectedStyles[] = $beer->getStyle();
            }
        }
        $this->potentialLocations = [];
        return $locationId;
    }

    public function findIdWithMostBeers()
    {
        $maxCount = 0;
        $maxId = array_keys($this->potentialLocations)[0];

        foreach ($this->potentialLocations as $locationId => $beers) {
            $beerCount = \count($beers);
            if ($beerCount > $maxCount) {
                $maxCount = $beerCount;
                $maxId = $locationId;
            }
        }

        return $maxId;
    }
}