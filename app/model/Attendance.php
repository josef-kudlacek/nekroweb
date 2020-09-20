<?php


namespace App\model;

use App\MyAuthenticator;
use Nette;

class Attendance
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getAttendanceByStudent($userId, $classId)
    {
        return $this->database->query('
            SELECT lesson.Number, lesson.Name AS LessonName, attendance.AttendanceDate,
            atype.Name AS Attendance, atype.Id AS AttendanceTypeId, attendance.ActivityPoints, attendance.AttendanceCard
            FROM attendance
            INNER JOIN student
            ON student.UserId = attendance.StudentUserId
            AND student.ClassId = attendance.StudentClassId
            INNER JOIN lesson
            ON lesson.Id = attendance.LessonId
            INNER JOIN attendancetype atype
            ON attendance.AttendanceTypeId = atype.Id
            WHERE student.UserId = ?
            AND student.ClassId = ?
            ORDER BY attendance.AttendanceDate;',
            $userId, $classId);
    }

    public function getAttendanceByClass($classId)
    {
        return $this->database->query('
            SELECT user.Name, student.HouseId,
            SUM(IF(lesson.Number = 1, attendance.ActivityPoints, NULL)) AS Lesson1AP,
            GROUP_CONCAT(IF(lesson.Number = 1, atype.Id, NULL)) AS Lesson1AT,
            SUM(IF(lesson.Number = 2, attendance.ActivityPoints, NULL)) AS Lesson2AP,
            GROUP_CONCAT(IF(lesson.Number = 2, atype.Id, NULL)) AS Lesson2AT,
            SUM(IF(lesson.Number = 3, attendance.ActivityPoints, NULL)) AS Lesson3AP,
            GROUP_CONCAT(IF(lesson.Number = 3, atype.Id, NULL)) AS Lesson3AT,
            SUM(IF(lesson.Number = 4, attendance.ActivityPoints, NULL)) AS Lesson4AP,
            GROUP_CONCAT(IF(lesson.Number = 4, atype.Id, NULL)) AS Lesson4AT,
            SUM(IF(lesson.Number = 5, attendance.ActivityPoints, NULL)) AS Lesson5AP,
            GROUP_CONCAT(IF(lesson.Number = 5, atype.Id, NULL)) AS Lesson5AT,
            SUM(IF(lesson.Number = 6, attendance.ActivityPoints, NULL)) AS Lesson6AP,
            GROUP_CONCAT(IF(lesson.Number = 6, atype.Id, NULL)) AS Lesson6AT,
            SUM(IF(lesson.Number = 7, attendance.ActivityPoints, NULL)) AS Lesson7AP,
            GROUP_CONCAT(IF(lesson.Number = 7, atype.Id, NULL)) AS Lesson7AT,
            SUM(IF(lesson.Number = 8, attendance.ActivityPoints, NULL)) AS Lesson8AP,
            GROUP_CONCAT(IF(lesson.Number = 8, atype.Id, NULL)) AS Lesson8AT,
            SUM(IF(lesson.Number = 9, attendance.ActivityPoints, NULL)) AS Lesson9AP,
            GROUP_CONCAT(IF(lesson.Number = 9, atype.Id, NULL)) AS Lesson9AT,
            SUM(IF(lesson.Number = 10, attendance.ActivityPoints, NULL)) AS Lesson10AP,
            GROUP_CONCAT(IF(lesson.Number = 10, atype.Id, NULL)) AS Lesson10AT,
            SUM(IF(lesson.Number = 11, attendance.ActivityPoints, NULL)) AS Lesson11AP,
            GROUP_CONCAT(IF(lesson.Number = 11, atype.Id, NULL)) AS Lesson11AT,
            SUM(IF(lesson.Number = 12, attendance.ActivityPoints, NULL)) AS Lesson12AP,
            GROUP_CONCAT(IF(lesson.Number = 12, atype.Id, NULL)) AS Lesson12AT,
            SUM(IF(lesson.Number = 13, attendance.ActivityPoints, NULL)) AS Lesson13AP,
            GROUP_CONCAT(IF(lesson.Number = 13, atype.Id, NULL)) AS Lesson13AT,
            SUM(IF(lesson.Number = 14, attendance.ActivityPoints, NULL)) AS Lesson14AP,
            GROUP_CONCAT(IF(lesson.Number = 14, atype.Id, NULL)) AS Lesson14AT
            FROM attendance
            INNER JOIN student
            ON student.UserId = attendance.StudentUserId
            AND student.ClassId = attendance.StudentClassId
            INNER JOIN user
            ON user.Id = student.UserId
            INNER JOIN lesson
            ON lesson.Id = attendance.LessonId
            INNER JOIN attendancetype atype
            ON attendance.AttendanceTypeId = atype.Id
            AND student.ClassId = ?
            GROUP BY user.Name
            ORDER BY user.Name, attendance.AttendanceDate;',
                $classId);
    }
}