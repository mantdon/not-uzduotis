<?php

namespace App\Util;


 class Haversine
 {
     private const earthRadius = 6371;

     private $from;
     private $to;

     public function __construct(Coordinates $from = null, Coordinates $to = null)
     {
         $this->from = $from;
         $this->to = $to;
     }

     public function setFromCoordinates(Coordinates $from): self
     {
         $this->from = $from;
         return $this;
     }

     public function setToCoordinates(Coordinates $to): self
     {
         $this->to = $to;
         return $this;
     }

     /**
      * @return float Distance in kilometers between the point specified with setFromCoordinate()
      * and the point specified with setToCoordinate().
      * @throws BadMethodCallException if called when any coordinate is not set.
      */
     public function calculateDistance() : float
     {
         if(!$this->coordinatesAreSet()) {
             throw new \BadMethodCallException('Coordinates must be set before calculating distance.');
         }

         $latitudeDelta = $this->to->getLatitudeRad() - $this->from->getLatitudeRad();
         $longitudeDelta = $this->to->getLongitudeRad() - $this->from->getLongitudeRad();

         $angle = 2 * asin(
                          sqrt(
                              (sin($latitudeDelta / 2) ** 2) +
                              cos($this->from->getLatitudeRad()) *
                              cos($this->to->getLatitudeRad()) *
                              (sin($longitudeDelta / 2) ** 2)
                          )
                      );

         return $angle * self::earthRadius;
     }

     private function coordinatesAreSet() : bool
     {
         return $this->from !== null && $this->to !== null;
     }
 }