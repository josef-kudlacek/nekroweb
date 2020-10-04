<?php


namespace App\Utils;


class Functions
{
    public static function calculatePercentStars($starsCount, $starsSum)
    {
        return ($starsCount/$starsSum)*100;
    }
}