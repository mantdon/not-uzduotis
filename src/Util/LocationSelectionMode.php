<?php

namespace App\Util;

class LocationSelectionMode
{
    public const ClosestBrewery = 'clst';
    public const MostBeer = 'mstb';

    private const validValues = ['clst', 'mstb'];

    public static function modeIsValid(string $mode): bool
    {
        return \in_array($mode, self::validValues, true);
    }
}