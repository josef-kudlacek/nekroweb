<?php


namespace App\Model;

use Nette;

class StudyClass
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getClasses($firstLessonParameter, $lastLessonParameter)
    {
        return $this->database->query('
            SELECT class.Id AS ClassId, class.Name, class.TimeFrom, class.TimeTo, class.FirstLesson, class.LastLesson, 
            semester.Id AS SemesterId, semester.YearFrom, semester.YearTo,
            year.Id AS YearId, year.Number, year.CodeName
            FROM necromancy.class class
            INNER JOIN necromancy.semester semester
            ON class.SemesterId = semester.Id
            INNER JOIN necromancy.year year
            ON class.YearId = year.Id
            WHERE class.FirstLesson <= ?
            AND class.LastLesson >= ?;',
                $firstLessonParameter, $lastLessonParameter);
    }

    public function getAvailableClasses()
    {
        $currentDate = date("Y-m-d");
        return $this->getClasses($currentDate, $currentDate);
    }

    public function getClassesBySemester($semesterId)
    {
        return $this->database->query('
            SELECT class.Id AS ClassId, class.Name, class.TimeFrom, class.TimeTo, class.FirstLesson, class.LastLesson, 
            semester.Id AS SemesterId, semester.YearFrom, semester.YearTo,
            year.Id AS YearId, year.Number, year.CodeName
            FROM necromancy.class class
            INNER JOIN necromancy.semester semester
            ON class.SemesterId = semester.Id
            INNER JOIN necromancy.year year
            ON class.YearId = year.Id
            WHERE semester.Id = ?;',
                $semesterId);
    }
}