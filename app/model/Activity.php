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

    public function getStudentsActivity($classId, $lessonId)
    {
        return $this->database->query('
            SELECT DISTINCT attendance.Id AS AttendanceId,
            student.HouseId AS HouseId, student.UserId, user.Name AS UserName,
            SUM(activity.ActivityPoints) AS ActivityPointsOverall,
            MIN(IF(activitytype.Id = 1, activity.ActivityPoints, NULL)) AS Question,
            MIN(IF(activitytype.Id = 2, activity.ActivityPoints, NULL)) AS RPG,
            MIN(IF(activitytype.Id = 3, activity.ActivityPoints, NULL)) AS Discussion,
            MIN(IF(activitytype.Id = 4, activity.ActivityPoints, NULL)) AS YearCompetition,
            MIN(IF(activitytype.Id = 5, activity.ActivityPoints, NULL)) AS Spell,
            MIN(IF(activitytype.Id = 6, activity.ActivityPoints, NULL)) AS ExamDeath,
            MIN(IF(activitytype.Id = 7, activity.ActivityPoints, NULL)) AS Rememberall,
            MIN(IF(activitytype.Id = 8, activity.ActivityPoints, NULL)) AS Mistake
            FROM attendance
            INNER JOIN attendancetype
            ON attendancetype.Id = attendance.AttendanceTypeId
            LEFT JOIN activity
            ON activity.AttendanceId = attendance.Id
            LEFT JOIN activitytype
            ON activitytype.Id = activity.ActivityTypeId
            INNER JOIN student
            ON student.UserId = attendance.StudentUserId
            AND student.ClassId = attendance.StudentClassId
            INNER JOIN user
            ON user.Id = student.UserId
            WHERE attendance.StudentClassId = ?
            AND attendance.LessonId = ?
            AND attendancetype.Name NOT IN ("Omluveno", "Neomluveno", "Omluveno předem")
            GROUP BY attendance.Id
            ORDER BY user.Name;',
            $classId, $lessonId);
    }

    public function getStudentSum($studentId, $classId)
    {
        return $this->database->query('
            SELECT 
            (SELECT
            SUM(attendancetype.Points)
            FROM attendance
            INNER JOIN attendancetype
            ON attendance.AttendanceTypeId = attendancetype.Id
            WHERE attendance.StudentUserId = ?
            AND attendance.StudentClassId = ?
            GROUP BY attendance.StudentUserId
            ) AS attendancePoints,
            (
            SELECT
            SUM(activity.ActivityPoints)
            FROM attendance
            INNER JOIN attendancetype
            ON attendance.AttendanceTypeId = attendancetype.Id
            LEFT JOIN activity
            ON activity.AttendanceId = attendance.Id
            WHERE attendance.StudentUserId = ?
            AND attendance.StudentClassId = ?
            GROUP BY attendance.StudentUserId
            ) AS activityPoints; 
            ',
            $studentId, $classId, $studentId, $classId);
    }

    public function insertActivity($values)
    {
        foreach ($values as $key => $value)
        {
            $this->database->query('INSERT INTO activity', $values[$key],
                'ON DUPLICATE KEY UPDATE', $values[$key]);
        }
    }


}