<?php


namespace App\Utils;


class Functions
{
    public static function calculatePercentStars($starsCount, $starsSum)
    {
        return round(($starsCount/$starsSum), 2)*100;
    }
}