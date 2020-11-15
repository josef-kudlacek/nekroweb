<?php


namespace App\Utils;


class Filter
{
    public static function assessmentWeight($assessmentWeight)
    {
        $assessmentColor = 'default';

        switch ($assessmentWeight) {
            case 1:
                $assessmentColor = 'secondary';
                break;
            case 2:
                $assessmentColor = 'primary';
                break;
            case 3:
                $assessmentColor = 'success';
                break;
            default:
                $assessmentColor = 'warning';
        }

        return $assessmentColor;
    }

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
                $attendanceColor = 'secondary';
                break;
            case 4:
                $attendanceColor = 'danger';
                break;
            case 5:
                $attendanceColor = 'light';
                break;
            case 6:
                $attendanceColor = 'info';
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

    public static function markType($markId)
    {
        $markColor = 'dark';

        switch ($markId) {
            case 1:
                $markColor = 'danger';
                break;
            case 2:
                $markColor = 'success';
                break;
            case 3:
                $markColor = 'primary';
                break;
            case 4:
                $markColor = 'secondary';
                break;
            case 5:
                $markColor = 'warning';
                break;
            case 6:
                $markColor = 'light';
                break;
            default:
                $markColor = 'dark';
        }

        return $markColor;
    }

    public static function markColor($average)
    {
        $markColor = 'light';

        if ($average >= 17) {
            $markColor = 'danger';
            return $markColor;
        } elseif ($average >= 12.25) {
            $markColor = 'success';
            return $markColor;
        } elseif ($average >= 7.25) {
            $markColor = 'primary';
            return $markColor;
        } elseif ($average >= 3.25) {
            $markColor = 'secondary';
            return $markColor;
        }  else {
            $markColor = 'warning';
            return $markColor;
        }
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