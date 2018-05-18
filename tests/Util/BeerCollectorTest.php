<?php

namespace App\Tests;

use App\Entity\Beer;
use App\Entity\Brewery;
use App\Entity\Geocode;
use App\Entity\Style;
use App\Util\BeerCollector;
use PHPUnit\Framework\TestCase;

class BeerCollectorTest extends TestCase
{
    private $location;
    private $location2;

    public function testAllBeerTypesCollectedFromLocation(): void
    {
        $this->setupFirstLocation();

        $beerCollector = new BeerCollector();
        $beerCollector->collectFrom($this->location);

        $beers = $beerCollector->getCollectedBeerNames();
        $this->assertCount(2, $beers);
    }

    public function testOnlyMissingBeerTypesCollectedFromLocation(): void
    {
        $this->setupFirstLocation();
        $this->setupSecondLocation();

        $beerCollector = new BeerCollector();
        $beerCollector->collectFrom($this->location);
        $beerCollector->collectFrom($this->location2);

        $beers = $beerCollector->getCollectedBeerNames();
        $this->assertCount(3, $beers);
    }

    public function testMostBeersCollectedWithSingleLocation(): void
    {
        $this->setupFirstLocation();

        $beerCollector = new BeerCollector();
        $beerCollector->addBeers($this->location);
        $beerCollector->collectMost();

        $beers = $beerCollector->getCollectedBeerNames();
        self::assertCount(2, $beers);
    }

    public function testMostBeersCollectedWithMultipleLocations(): void
    {
        $this->setupFirstLocation();
        $this->setupSecondLocation();

        $beerCollector = new BeerCollector();
        $beerCollector->addBeers($this->location2);
        $beerCollector->addBeers($this->location);
        $beerCollector->collectMost();

        $beers = $beerCollector->getCollectedBeerNames();
        $this->assertCount(2, $beers);
    }

    public function testAllBeerStylesCollected()
    {
        $this->setupFirstLocation();

        $beerCollector = new BeerCollector();
        $beerCollector->collectFrom($this->location);

        $styles = $beerCollector->getCollectedStyleNames();
        $this->assertCount(2, $styles);
    }

    public function testOnlyMissingBeerStylesCollectedFromLocation(): void
    {
        $this->setupFirstLocation();
        $this->setupSecondLocation();

        $beerCollector = new BeerCollector();
        $beerCollector->collectFrom($this->location);
        $beerCollector->collectFrom($this->location2);

        $styles = $beerCollector->getCollectedStyleNames();
        $this->assertCount(3, $styles);
    }

    public function testAllStylesCollectedFromLocationWithMostBeers(): void
    {
        $this->setupFirstLocation();
        $this->setupSecondLocation();

        $beerCollector = new BeerCollector();
        $beerCollector->addBeers($this->location2);
        $beerCollector->addBeers($this->location);
        $beerCollector->collectMost();

        $styles = $beerCollector->getCollectedBeerNames();
        $this->assertCount(2, $styles);
    }

    private function setupFirstLocation(): void
    {
        $this->location = new Geocode();
        $brewery = new Brewery();
        $beer11 = new Beer();
        $beer12 = new Beer();
        $style11 = new Style();
        $style12 = new Style();
        $this->location->setId(0)->setBrewery($brewery);
        $brewery->setId(0)->addBeer($beer11)->addBeer($beer12);
        $style11->setId(0)->setName('Style1');
        $style12->setId(1)->setName('Style2');
        $beer11->setId(0)->setName('First')->setStyle($style11);
        $beer12->setId(1)->setName('Second')->setStyle($style12);
    }

    private function setupSecondLocation(): void
    {
        $this->location2 = new Geocode();
        $brewery2 = new Brewery();
        $beer21 = new Beer();
        $beer22 = new Beer();
        $style21 = new Style();
        $style22 = new Style();
        $this->location2->setId(1)->setBrewery($brewery2);
        $brewery2->setId(1)->addBeer($beer21)->addBeer($beer22);
        $style21->setId(1)->setName('Style1');
        $style22->setId(2)->setName('Style3');
        $beer21->setId(1)->setName('First')->setStyle($style21);
        $beer22->setId(2)->setName('Third')->setStyle($style22);
    }
}