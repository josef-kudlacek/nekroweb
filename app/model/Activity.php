<?php


namespace App\model;

use Nette;

class Activity
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getStudentsAttendance($classId, $lessonId)
    {
        return $this->database->query('
            SELECT attendance.Id AS AttendanceId, student.HouseId,
            user.Name AS UserName, user.Id AS UserId
            FROM attendance
            INNER JOIN attendancetype
            ON attendancetype.Id = attendance.AttendanceTypeId
            INNER JOIN student
            ON student.UserId = attendance.StudentUserId
            AND student.ClassId = attendance.StudentClassId
            INNER JOIN user
            ON user.Id = attendance.StudentUserId
            WHERE attendance.StudentClassId = ?
            AND attendance.LessonId = ?
            AND attendancetype.Name NOT IN ("Omluveno", "Neomluveno", "Omluveno předem")
            ORDER BY user.Name;',
            $classId, $lessonId);
    }

    public function insertActivity($values)
    {
        return $this->database->query('INSERT INTO activity', $values);
    }


}