<?php


namespace App\utils;


class Filter
{
    public static function attendanceType($attendanceTypeId)
    {
        $attendanceColor = 'default';

        switch ($attendanceTypeId) {
            case 1:
                $attendanceColor = 'success';
                break;
            case 2:
                $attendanceColor = 'warning';
                break;
            case 3:
                $attendanceColor = 'info';
                break;
            case 4:
                $attendanceColor = 'danger';
                break;
            case 5:
                $attendanceColor = 'default';
                break;
            default:
                $attendanceColor = 'dark';
        }

        return $attendanceColor;
    }

    public static function houseType($houseId)
    {
        $houseColor = 'default';

        switch ($houseId) {
            case 1:
                $houseColor = 'danger';
                break;
            case 2:
                $houseColor = 'primary';
                break;
            case 3:
                $houseColor = 'warning';
                break;
            case 4:
                $houseColor = 'success';
                break;
            case 5:
                $houseColor = 'info';
                break;
            default:
                $houseColor = 'dark';
        }

        return $houseColor;
    }

    public static function semesterType($YearTo)
    {
        $semesterColor = 'primary';

        if ($YearTo) {
            $semesterColor = 'secondary';
        }

        return $semesterColor;
    }

    public static function weekDayCZ($date)
    {
        $weekDay = array('Neděle', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota');
        return $weekDay[(integer)$date->format('w')];
    }

    public static function encodeToCharset($stringUrl, $charset)
    {
        $stringUrl = iconv("UTF-8", $charset, $stringUrl);
        return $stringUrl;
    }
}