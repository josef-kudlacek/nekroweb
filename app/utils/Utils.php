<?php


namespace App\utils;


class Utils
{
    public static function convertEmptyToNull($values)
    {
        foreach ($values as $key => $value)
        {
            if (is_array($value))
            {
                continue;
            }

            if (trim($value) === '' || $value  === 'null')
            {
                $values[$key] = NULL;
            }
        }
        return $values;
    }

    public static function prepareSelectBoxArray($databaseResultSet)
    {
        $resultsetArray = $databaseResultSet->fetchAll();

        $selectBoxArray = array();
        foreach ($resultsetArray as $key => $value)
        {
            $string = $value['Name']. ' (' . $value['YearFrom'];
            if ($value['YearTo'])
            {
                $string = $string . '/' . $value['YearTo'];
            }

            $selectBoxArray[$value['ClassId']] = $string . ')';
        }

        return $selectBoxArray;
    }

    public static function prepareSemesterSelectBoxArray($databaseResultSet)
    {
        $resultsetArray = $databaseResultSet->fetchAll();

        $selectBoxArray = array();
        foreach ($resultsetArray as $key => $value)
        {
            $string = $value['YearFrom'];
            if ($value['YearTo'])
            {
                $string = $string . '/' . $value['YearTo'];
            }

            $selectBoxArray[$value['SemesterId']] = $string;
        }

        return $selectBoxArray;
    }

    public static function setActualSemester($userIdentity, $actualSemester)
    {
        $userIdentity->semesterId = $actualSemester->Id;
        $userIdentity->semesterFrom = $actualSemester->YearFrom;
        $userIdentity->semesterTo = $actualSemester->YearTo;
    }

    public static function generateString($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public static function sendEmail($recipient, $subject, $newPassword)
    {
        $to = $recipient;
        $subject = $subject;

        $message = "<p>Dobrý den,</p><p>Bylo Vám vygenerováno nové heslo: <strong>".$newPassword."</strong><br />
                S pozdravem a přáním pěkného dne,<br /><a href=https://www.nekromancie.eu>www.nekromancie.eu</a></p>";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: profesor Richard Bufler <joseph.kudlacek@gmail.com>' . "\r\n";

        mail($to,$subject,$message,$headers);
    }
}