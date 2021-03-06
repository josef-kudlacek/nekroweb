<?php


namespace App\Utils;

define ('FILE_ABSOLUTE_PATH', '/home/users/bufler/nekromancie.eu/web/www');

class Utils
{
    public static function convertEmptyToNull($values)
    {
        foreach ($values as $key => $value)
        {
            if (is_array($value))
            {
                $values[$key] = Utils::convertEmptyToNull($value);
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
        $sender = "\"Profesor Richard Bufler\" <joseph.kudlacek@gmail.com>";

        $body = "<html>";
        $body .= "<body style=\"font-family:Arial; \">";
        $body .= "<p>Dobrý den,<br />";
        $body .= "Na základě Vašeho zapomenutého hesla Vám bylo vygenerováno heslo nové: <strong>".$newPassword."</strong>.<br />";
        $body .= "Heslo si můžete kdykoliv změnit v nastavení.</p>";
        $body .= "<p><i>S pozdravem a přáním pěkného dne,<br /> R.B.</i><br />";
        $body .= "<a href=https://www.nekromancie.eu>www.nekromancie.eu</a></p>";
        $body .= "</body>";
        $body .= "</html>";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "Content-Transfer-Encoding: 7bit" . "\r\n";
        $headers .= "Message-ID: <" . time() . "." . md5($sender . $recipient) . "@fenek.stable.cz>" . "\r\n";
        $headers .= "From: " . $sender . "\r\n";

        mail($recipient, $subject, $body, $headers);
    }

    public static function getAbsolutePath()
    {
        return FILE_ABSOLUTE_PATH;
    }
}