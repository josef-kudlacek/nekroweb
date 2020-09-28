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
            year.Id AS YearId, year.Number, year.CodeName, COUNT(student.UserId) AS StudentsCount
            FROM necromancy.class class
            INNER JOIN necromancy.semester semester
            ON class.SemesterId = semester.Id
            INNER JOIN necromancy.year year
            ON class.YearId = year.Id
            LEFT JOIN student
            ON student.ClassId = class.Id
            WHERE semester.Id = ?
            GROUP BY class.Id
            ORDER BY class.Name;',
                $semesterId);
    }

    public function getClassById($studyClassId)
    {
        return $this->database->query('
            SELECT class.Id AS ClassId, class.Name, class.TimeFrom, class.TimeTo,
            DATE_FORMAT(class.FirstLesson, "%Y-%m-%d") AS FirstLesson, 
            DATE_FORMAT(class.LastLesson, "%Y-%m-%d") AS LastLesson, 
            semester.Id AS SemesterId, semester.YearFrom, semester.YearTo,
            year.Id AS YearId, year.Number, year.CodeName, COUNT(student.UserId) AS StudentsCount
            FROM necromancy.class class
            INNER JOIN necromancy.semester semester
            ON class.SemesterId = semester.Id
            INNER JOIN necromancy.year year
            ON class.YearId = year.Id
            LEFT JOIN student
            ON student.ClassId = class.Id
            WHERE class.Id = ?
            GROUP BY class.Id;',
            $studyClassId);
    }

    public function getStudentClassById($studyClassId, $studentId)
    {
        return $this->database->query('
            SELECT class.Id AS ClassId, class.Name, class.TimeFrom, class.TimeTo,
            DATE_FORMAT(class.FirstLesson, "%Y-%m-%d") AS FirstLesson, 
            DATE_FORMAT(class.LastLesson, "%Y-%m-%d") AS LastLesson, 
            semester.Id AS SemesterId, semester.YearFrom, semester.YearTo,
            year.Id AS YearId, year.Number, year.CodeName, COUNT(student.UserId) AS StudentsCount
            FROM necromancy.class class
            INNER JOIN necromancy.semester semester
            ON class.SemesterId = semester.Id
            INNER JOIN necromancy.year year
            ON class.YearId = year.Id
            INNER JOIN student
            ON student.ClassId = class.Id
            WHERE class.Id = ?
            AND student.UserId = ?
            GROUP BY class.Id;',
            $studyClassId, $studentId);
    }

    public function insertClass($values)
    {
        return $this->database->query('
            INSERT INTO class (Name, FirstLesson, LastLesson, TimeFrom, TimeTo, SemesterId, YearId)
            VALUES
            (?, ?, ?, ?, ?, ?, ?);',
                $values->name, $values->firstlesson, $values->lastlesson, $values->timefrom,
                $values->timeto, $values->semester, $values->year);
    }

    public function updateClassById($values, $studyClassId)
    {
        return $this->database->query('
            UPDATE class
            SET Name = ?,
            FirstLesson = ?,
            LastLesson = ?,
            TimeFrom = ?,
            TimeTo = ?,
            SemesterId = ?,
            YearId = ?
            WHERE Id = ?;',
            $values->name, $values->firstlesson, $values->lastlesson, $values->timefrom,
            $values->timeto, $values->semester, $values->year, $studyClassId);
    }

    public function deleteClassById($studyClassId)
    {
        return $this->database->query('
            DELETE
            FROM class
            WHERE Id = ?;',
                $studyClassId);
    }
}