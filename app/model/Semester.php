<?php


namespace App\model;

use Nette;

class Semester
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function GetActualSemester()
    {
        return $this->database->query('
            SELECT semester.Id, semester.YearFrom, semester.YearTo
            FROM semester
            ORDER BY semester.YearFrom DESC, semester.YearTo DESC
            LIMIT 1;')->fetch();
    }

    public function GetSemesters()
    {
        return $this->database->query('
            SELECT semester.Id AS SemesterId, semester.YearFrom, semester.YearTo
            FROM semester
            ORDER BY semester.YearFrom DESC, semester.YearTo DESC;');
    }
}