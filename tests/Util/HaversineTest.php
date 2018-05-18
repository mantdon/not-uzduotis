<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Util\Haversine;
use App\Util\Coordinates;

class HaversineTest extends TestCase
{
    public function testExceptionThrownOnEmptyCoordinates(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $haversine = new Haversine();
        $haversine->calculateDistance();
    }

    public function testDistanceCalculation(): void
    {
        $fromLatitude = 51.376095;
        $fromLongitude = 8.176321;
        $toLatitude = 49.118077;
        $toLongitude = 14.592347;

        $expectedDistance = 520.652;

        $from = new Coordinates($fromLatitude, $fromLongitude);
        $to = new Coordinates($toLatitude, $toLongitude);

        $haversine = new Haversine($from, $to);
        $calculatedDistance = $haversine->calculateDistance();

        $this->assertEquals($expectedDistance, $calculatedDistance, '', 0.2);
    }

    public function testReverseDistanceCalculation(): void
    {
        $fromLatitude = 51.376095;
        $fromLongitude = 8.176321;
        $toLatitude = 49.118077;
        $toLongitude = 14.592347;

        $expectedDistance = 520.652;

        $from = new Coordinates($fromLatitude, $fromLongitude);
        $to = new Coordinates($toLatitude, $toLongitude);

        $haversine = new Haversine($from, $to);
        $calculatedDistance = $haversine->calculateDistance();

        $this->assertEquals($expectedDistance, $calculatedDistance, '', 0.2);
    }
}