<?php


namespace App\Model;

use Nette;

class Arc
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getArcName($fileName)
    {
        return $this->database->query('
            SELECT attendance.AttendanceDate, class.Name AS ClassName, 
            lesson.Number AS LessonNumber, lesson.Name AS LessonName
            FROM arc
            INNER JOIN attendance
            ON arc.ClassId = attendance.StudentClassId
            AND arc.LessonId = attendance.LessonId
            INNER JOIN class
            ON attendance.StudentClassId = class.Id
            INNER JOIN lesson
            ON attendance.LessonId = lesson.Id
            WHERE arc.FileName = ?
            GROUP BY arc.FileName;',
            $fileName);
    }

    public function getArcsByAttendance($classId, $lessonId)
    {
        $params = array(
            ['attendance.StudentClassId' => $classId],
            ['attendance.LessonId' => $lessonId],
        );

        return $this->getArcsByParams($params);
    }

    public function getArcsByClass($classId)
    {
        $params = array(
            ['attendance.StudentClassId' => $classId],
        );

        return $this->getArcsByParams($params);
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

    private function getArcsByParams($params)
    {
        return $this->database->query('
            SELECT attendance.AttendanceDate, lesson.Number AS LessonNumber, lesson.Name AS LessonName,
            arc.FileName, class.Name AS ClassName, lesson.Id AS LessonId, class.Id AS ClassId
            FROM attendance
            INNER JOIN lesson
            ON attendance.LessonId = lesson.Id
            INNER JOIN class
            ON attendance.StudentClassId = class.Id
            LEFT JOIN arc
            ON arc.ClassId = attendance.StudentClassId
            AND arc.LessonId = attendance.LessonId
            WHERE',
            $params,
            'GROUP BY lesson.Number
            ORDER BY lesson.Number;');
    }
}