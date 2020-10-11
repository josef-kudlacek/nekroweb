<?php


namespace App\Model;

use Nette;

class Year
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getYears()
    {
        return $this->database->query('
            SELECT year.Id, year.Number, year.CodeName
            FROM year          
            ORDER BY Number, CodeName;');
    }

    public function getYearsBySemester($semesterId)
    {
        return $this->database->query('
            SELECT year.*
            FROM class
            INNER JOIN year
            ON class.YearId = year.Id
            WHERE class.SemesterId = ?
            ORDER BY year.Number;',
                $semesterId);
    }
}