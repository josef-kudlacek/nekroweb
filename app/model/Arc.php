<?php


namespace App\model;

use Nette;

class Arc
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getArcsByClass($classId)
    {
        return $this->database->query('
            SELECT attendance.AttendanceDate, lesson.Number AS LessonNumber, lesson.Name AS LessonName,
            arc.FileName, class.Name AS ClassName
            FROM attendance
            INNER JOIN lesson
            ON attendance.LessonId = lesson.Id
            INNER JOIN class
            ON attendance.StudentClassId = class.Id
            LEFT JOIN arc
            ON arc.ClassId = attendance.StudentClassId
            AND arc.LessonId = attendance.LessonId
            WHERE attendance.StudentClassId = ?
            GROUP BY lesson.Number
            ORDER BY lesson.Number;',
            $classId);
    }

    public function insertArc($values)
    {
        return $this->database->query('
            INSERT INTO arc
            VALUES (?, ?, ?);',
            $values->ClassId, $values->LessonId, $values->FileName);
    }

    public function deleteArc($arcName)
    {
        return $this->database->query('
            DELETE
            FROM arc
            WHERE arc.FileName = ?;',
            $arcName);
    }
}