<?php


namespace App\Model;

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

    public function GetSemesterById($semesterId)
    {
        return $this->database->query('
            SELECT semester.Id AS SemesterId, semester.YearFrom, semester.YearTo
            FROM semester
            WHERE semester.Id = ?;',
                $semesterId);
    }

    public function insertSemester($values)
    {
        return $this->database->query('
            INSERT INTO semester (YearFrom, YearTo)
            VALUES
            (?, ?);',
            $values->YearFrom, $values->YearTo);
    }

    public function updateSemester($values, $semesterId)
    {
        return $this->database->query('
            UPDATE semester
            SET YearFrom = ?,
            YearTo = ?
            WHERE Id = ?;',
            $values->YearFrom, $values->YearTo, $semesterId);
    }
}