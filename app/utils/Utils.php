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
            $selectBoxArray[$value['Id']] = $value['Name']. ' (' . $value['YearFrom'] . '/' . $value['YearTo'] . ')';
        }

        return $selectBoxArray;
    }
}