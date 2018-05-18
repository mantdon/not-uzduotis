<?php

namespace App;

use App\Entity\Geocode;
use App\Util\BeerCollector;
use App\Util\Haversine;
use App\Util\LocationSelectionMode;
use App\Util\Messages;
use App\Util\MessageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\UnsetKeyException;

class Program
{
    // Fallback default constants in case setters are not used.
    public const defaultStartLatitude = 51.355468;
    public const defaultStartLongitude = 11.100790;
    public const defaultMaxDistance = 2000;
    public const defaultSearchRadius = 2;
    public const defaultDistanceDelta = 50;

    /** @var EntityManagerInterface */
    private $em;
    private $haversine;
    private $beerCollector;
    private $messages;

    /** @var Geocode */
    private $home;
    private $maxDistance;
    private $searchRadius;
    private $mode;
    private $distanceDelta;

    private $distanceTravelled;

    /** @var Geocode */
    private $currentLocation;
    /** @var Geocode */
    private $nextLocation;
    private $distances;
    private $coordinateCount;
    private $abort;
    private $initialized;
    private $savedSearchRadius;

    public function __construct(EntityManagerInterface $em, BeerCollector $beerCollector, Messages $messages)
    {
        $this->em = $em;
        $this->beerCollector = $beerCollector;
        $this->messages = $messages;
        $this->haversine = new Haversine();

        $this->initializeParameters();
    }

    private function initializeParameters(): void
    {
        $this->home = new Geocode();
        $this->home->setId(0);
        $this->home->setLatitude(self::defaultStartLatitude);
        $this->home->setLongitude(self::defaultStartLongitude);
        $this->maxDistance = self::defaultMaxDistance;
        $this->searchRadius = self::defaultSearchRadius;
        $this->mode = LocationSelectionMode::ClosestBrewery;
        $this->distanceDelta = self::defaultDistanceDelta;

        $this->currentLocation = $this->home;
        $this->distanceTravelled = 0;
        $this->coordinateCount = $this->em->getRepository('App:Geocode')->getCount();
        $this->abort = false;
        $this->initialized = false;
    }

    public function getDistanceTravelled(): float
    {
        return $this->distanceTravelled;
    }

    public function getNumberOfBreweriesVisited(): int
    {
        // Exclude the last location as it has not been visited yet.
        return \count(array_keys($this->distances)) - 1;
    }

    public function getCollectedBeerNames($sorted = false): array
    {
        return $this->beerCollector->getCollectedBeerNames($sorted);
    }

    public function getCollectedStyleNames($sorted = false): array
    {
        return $this->beerCollector->getCollectedStyleNames($sorted);
    }

    public function wasAborted()
    {
        return $this->abort;
    }

    public function getDistances()
    {
        return $this->distances;
    }

    public function getMessage(int $messageType = MessageType::Notification): ?string
    {
        return $this->messages->getMessage($messageType);
    }

    public function setStartLatitude(float $latitude): self
    {
        $this->home->setLatitude($latitude);
        return $this;
    }

    public function setStartLongitude(float $longitude): self
    {
        $this->home->setLongitude($longitude);
        return $this;
    }

    public function setMaxDistance($maxDistance)
    {
        $this->maxDistance = $maxDistance;
        return $this;
    }

    public function setNextLocationSelectionMode(string $mode)
    {
        $this->mode = $mode;
        return $this;
    }

    public function setSearchRadius(float $radius)
    {
        $this->searchRadius = $radius;
        return $this;
    }

    public function setLocationSelectionDistanceDelta(float $distanceDelta)
    {
        $this->distanceDelta = $distanceDelta;
        return $this;
    }

    public function hasSolution(): bool
    {
        if (!$this->initialized) {
            throw new \BadMethodCallException('Cannot check if solution exists before Initialize() has been called.');
        }

        $closestLocation = $this->getClosestLocationTo($this->home);
        $minDistance = $this->getCalculatedDistance($this->home, $closestLocation);
        // Enough distance to reach the location and come back.
        if ($minDistance * 2 > $this->maxDistance) {
            $this->messages->setNoSolutionMessage($closestLocation, $minDistance, $this->maxDistance);
            return false;
        }
        return true;
    }

    private function getClosestLocationTo(Geocode $location)
    {
        if (array_key_exists($location->getId(), $this->distances)) {
            // Array is sorted in ascending order by value.
            // First key corresponds to the minimum distance value.
            $minKey = array_keys($this->distances[$location->getId()])[0];
            return $this->findLocation($minKey);
        }
        throw new UnsetKeyException('The specified location has not been visited yet.');
    }

    private function findLocation(int $id)
    {
        return $this->em->getRepository('App:Geocode')->find($id);
    }

    private function getCalculatedDistance(Geocode $fromLocation, Geocode $toLocation): float
    {
        return $this->distances[$fromLocation->getId()][$toLocation->getId()];
    }

    public function canReachNextLocation(): bool
    {
        if ($this->abort) {
            return false;
        }

        $distanceToNextLocation = $this->getCalculatedDistance($this->currentLocation, $this->nextLocation);
        $distanceHomeFromNextLocation = $this->calculateDistance($this->nextLocation, $this->home);

        if ($distanceToNextLocation + $distanceHomeFromNextLocation < $this->maxDistance - $this->distanceTravelled) {
            if ($this->mode === LocationSelectionMode::ClosestBrewery && $this->currentLocation->hasBrewery()) {
                $this->beerCollector->collectFrom($this->currentLocation);
            } elseif ($this->mode === LocationSelectionMode::MostBeer) {
                $this->beerCollector->collectMost();
            }
            return true;
        }
        return false;
    }

    private function calculateDistance(Geocode $fromLocation, Geocode $toLocation): float
    {
        return $this->haversine->setFromCoordinates($fromLocation->getCoordinates())
                               ->setToCoordinates($toLocation->getCoordinates())->calculateDistance();
    }

    public function Initialize(): void
    {
        $this->findPossibleLocations($this->home);
        $this->pickNextLocation();
        $this->messages->setTravelMessage($this->home, 0);
        $this->initialized = true;
    }

    public function findPossibleLocations(Geocode $fromLocation): void
    {
        for ($i = 1; $i < $this->coordinateCount; $i++) {
            $toLocation = $this->findLocation($i);
            if ($toLocation !== null &&
                $i !== $fromLocation->getId() &&
                !$this->visited($toLocation) &&
                $this->withinSearchRadius($fromLocation, $toLocation))
            {
                $distance = $this->calculateDistance($fromLocation, $toLocation);
                // Some geocode entries have different properties but identical coordinates,
                // any locations with the same coordinates as the one being analyzed and locations
                // without a relation to a brewery should be ignored.
                if ($distance !== 0.0 && $toLocation->hasBrewery()) {
                    $this->saveCalculatedDistance($fromLocation, $toLocation, $distance);
                }
            }
        }
        $this->repeatIfNoLocationsFound($fromLocation);
    }

    private function visited(Geocode $location): bool
    {
        return isset($this->distances[$location->getId()]);
    }

    private function withinSearchRadius(Geocode $referencePoint, Geocode $target): bool
    {
        $referencePointCoordinates = $referencePoint->getCoordinates();
        $targetCoordinates = $target->getCoordinates();
        $latitudeDelta = $targetCoordinates->getLatitudeRad() - $referencePointCoordinates->getLatitudeRad();
        $longitudeDelta = $targetCoordinates->getLongitudeRad() - $referencePointCoordinates->getLongitudeRad();

        return abs($latitudeDelta) <= deg2rad($this->searchRadius) && abs($longitudeDelta) <= deg2rad($this->searchRadius);
    }

    private function saveCalculatedDistance(Geocode $fromLocation, Geocode $toLocation, float $distance): void
    {
        $this->distances[$fromLocation->getId()][$toLocation->getId()] = $distance;
    }

    private function repeatIfNoLocationsFound(Geocode $fromLocation): void
    {
        $this->saveSearchRadiusIfNoneSaved($this->searchRadius);
        if (empty($this->distances[$fromLocation->getId()])) {
            if ($this->searchRadius < 90) {
                $this->searchRadius += 2;
                $this->findPossibleLocations($fromLocation);
            }
        } else {
            asort($this->distances[$fromLocation->getId()]);
            $this->restoreSearchRadius();
        }
    }

    private function saveSearchRadiusIfNoneSaved(float $searchRadius): void
    {
        if ($this->savedSearchRadius === null) {
            $this->savedSearchRadius = $searchRadius;
        }
    }

    private function restoreSearchRadius(): void
    {
        if ($this->savedSearchRadius === null) {
            throw new \BadMethodCallException('No search radius is saved.');
        }

        $this->searchRadius = $this->savedSearchRadius;
        $this->savedSearchRadius = null;
    }

    private function pickNextLocation(): void
    {
        try {
            if ($this->mode === LocationSelectionMode::ClosestBrewery) {
                $this->nextLocation = $this->getClosestLocationTo($this->currentLocation);
            } elseif ($this->mode === LocationSelectionMode::MostBeer) {
                $locationWithMostBeerTypes = $this->pickNextLocationWithMostBeerTypes($this->currentLocation);
                $this->nextLocation = $this->findLocation($locationWithMostBeerTypes);
            }
        } catch (UnsetKeyException $e) {
            $this->messages->setNoMoreLocationsMessage();
            $this->abort = true;
        }
    }

    private function pickNextLocationWithMostBeerTypes(Geocode $location)
    {
        if (!isset($this->distances[$location->getId()])) {
            throw new UnsetKeyException('The specified location has not been visited yet.');
        }

        $minKey = array_keys($this->distances[$location->getId()])[0];
        $closestLocation = $this->findLocation($minKey);
        $dist = $this->getCalculatedDistance($this->currentLocation, $closestLocation);


        $this->beerCollector->addBeers($closestLocation);

        foreach ($this->distances[$this->currentLocation->getId()] as $locationId => $distance) {
            if ($distance > $dist + $this->distanceDelta || ($dist + $this->distanceDelta) * 2 > $this->maxDistance) {
                break;
            }
            if ($minKey !== $locationId) {
                $this->beerCollector->addBeers($this->findLocation($locationId));
            }
        }
        return $this->beerCollector->findIdWithMostBeers();
    }

    public function nextLocation(): void
    {
        $this->updateDistanceTravelled();
        $this->updateCurrentLocation();
        $this->findPossibleLocations($this->currentLocation);
        $this->pickNextLocation();
    }

    private function updateDistanceTravelled(): void
    {
        $distance = $this->getCalculatedDistance($this->currentLocation, $this->nextLocation);
        $this->distanceTravelled += $distance;
    }

    private function updateCurrentLocation(): void
    {
        $distanceToNextLocation = $this->getCalculatedDistance($this->currentLocation, $this->nextLocation);
        $this->messages->setTravelMessage($this->nextLocation, $distanceToNextLocation);

        $this->currentLocation = $this->nextLocation;
        $this->nextLocation = null;
    }

    public function returnHome(): void
    {
        $distanceHome = $this->calculateDistance($this->currentLocation, $this->home);
        $this->distanceTravelled += $distanceHome;
        $this->currentLocation = null;
        $this->messages->setTravelMessage($this->home, $distanceHome, true);
    }
}