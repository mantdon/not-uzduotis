<?php

namespace App\Util;

class Coordinates
{
    private $latitude;
    private $longitude;

    /**
     * @param float $latitude coordinate in degrees.
     * @param float $longitude coordinate in degrees.
     */
    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * @param float $latitude coordinate in degrees.
     * @return Coordinates
     */
    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * @param float $longitude coordinate in degrees.
     * @return Coordinates
     */
    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * @return float Latitude coordinate in degrees.
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * @return float Longitude coordinate in degrees.
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * @return float Latitude coordinate in radians.
     */
    public function getLatitudeRad(): float
    {
        return deg2rad($this->latitude);
    }

    /**
     * @return float Longitude coordinate in radians.
     */
    public function getLongitudeRad(): float
    {
        return deg2rad($this->longitude);
    }
}