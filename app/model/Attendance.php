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
            SELECT house.Id AS HouseId, user.Name AS StudentName,
            attendance.AttendanceTypeId, attendancetype.Name AS AttendanceName, 
            lesson.Number AS LessonNumber, lesson.Name AS LessonName, attendance.AttendanceDate,
            SUM(ActivityPoints) AS ActivityOverall, 
            GROUP_CONCAT(CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(", activitytype.Name, ")"))
                    SEPARATOR " + ") AS ActivityDescription,
            activity.ActivityPoints, attendance.AttendanceCard
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
            INNER JOIN lesson
            ON lesson.Id = attendance.LessonId
            INNER JOIN class
            ON class.Id = attendance.StudentClassId
            INNER JOIN user
            ON user.Id = student.UserId
            INNER JOIN house
            ON student.HouseId = house.Id
            WHERE student.UserId = ?
            AND student.ClassId = ?
            GROUP BY user.Name
            ORDER BY lesson.Number;',
                $userId, $classId);
    }

    public function getAttendanceByClass($classId)
    {
        return $this->database->query('
            SELECT DISTINCT house.Id AS HouseId, user.Id, user.Name AS StudentName,
            MIN(IF(lesson.Number = 1, attendancetype.Id, NULL)) AS Lesson1AT,
            MIN(IF(lesson.Number = 1, attendancetype.Points, NULL)) AS Lesson1ATP,
            SUM(IF(lesson.Number = 1, activity.ActivityPoints, NULL)) AS Lesson1AP,
            GROUP_CONCAT(IF(lesson.Number = 1, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            SEPARATOR " + ") AS Lesson1Desc,
            MIN(IF(lesson.Number = 1, attendance.AttendanceCard, NULL)) AS Lesson1Card,
            MIN(IF(lesson.Number = 2, attendancetype.Id, NULL)) AS Lesson2AT,
            MIN(IF(lesson.Number = 2, attendancetype.Points, NULL)) AS Lesson2ATP,
            SUM(IF(lesson.Number = 2, activity.ActivityPoints, NULL)) AS Lesson2AP,
            GROUP_CONCAT(IF(lesson.Number = 2, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            SEPARATOR " + ") AS Lesson2Desc,
            MIN(IF(lesson.Number = 2, attendance.AttendanceCard, NULL)) AS Lesson2Card,
            MIN(IF(lesson.Number = 3, attendancetype.Id, NULL)) AS Lesson3AT,
            MIN(IF(lesson.Number = 3, attendancetype.Points, NULL)) AS Lesson3ATP,
            SUM(IF(lesson.Number = 3, activity.ActivityPoints, NULL)) AS Lesson3AP,
            GROUP_CONCAT(IF(lesson.Number = 3, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            SEPARATOR " + ") AS Lesson3Desc,
            MIN(IF(lesson.Number = 3, attendance.AttendanceCard, NULL)) AS Lesson3Card,
            MIN(IF(lesson.Number = 4, attendancetype.Id, NULL)) AS Lesson4AT,
            MIN(IF(lesson.Number = 4, attendancetype.Points, NULL)) AS Lesson4ATP,
            SUM(IF(lesson.Number = 4, activity.ActivityPoints, NULL)) AS Lesson4AP,
            GROUP_CONCAT(IF(lesson.Number = 4, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            SEPARATOR " + ") AS Lesson4Desc,
            MIN(IF(lesson.Number = 4, attendance.AttendanceCard, NULL)) AS Lesson4Card,
            MIN(IF(lesson.Number = 5, attendancetype.Id, NULL)) AS Lesson5AT,
            MIN(IF(lesson.Number = 5, attendancetype.Points, NULL)) AS Lesson5ATP,
            SUM(IF(lesson.Number = 5, activity.ActivityPoints, NULL)) AS Lesson5AP,
            GROUP_CONCAT(IF(lesson.Number = 5, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            SEPARATOR " + ") AS Lesson5Desc,
            MIN(IF(lesson.Number = 5, attendance.AttendanceCard, NULL)) AS Lesson5Card,
            MIN(IF(lesson.Number = 6, attendancetype.Id, NULL)) AS Lesson6AT,
            MIN(IF(lesson.Number = 6, attendancetype.Points, NULL)) AS Lesson6ATP,
            SUM(IF(lesson.Number = 6, activity.ActivityPoints, NULL)) AS Lesson6AP,
            GROUP_CONCAT(IF(lesson.Number = 6, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            SEPARATOR " + ") AS Lesson6Desc,
            MIN(IF(lesson.Number = 6, attendance.AttendanceCard, NULL)) AS Lesson6Card,
            MIN(IF(lesson.Number = 7, attendancetype.Id, NULL)) AS Lesson7AT,
            MIN(IF(lesson.Number = 7, attendancetype.Points, NULL)) AS Lesson7ATP,
            SUM(IF(lesson.Number = 7, activity.ActivityPoints, NULL)) AS Lesson7AP,
            GROUP_CONCAT(IF(lesson.Number = 7, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            SEPARATOR " + ") AS Lesson7Desc,
            MIN(IF(lesson.Number = 7, attendance.AttendanceCard, NULL)) AS Lesson7Card,
            MIN(IF(lesson.Number = 8, attendancetype.Id, NULL)) AS Lesson8AT,
            MIN(IF(lesson.Number = 8, attendancetype.Points, NULL)) AS Lesson8ATP,
            SUM(IF(lesson.Number = 8, activity.ActivityPoints, NULL)) AS Lesson8AP,
            GROUP_CONCAT(IF(lesson.Number = 8, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            SEPARATOR " + ") AS Lesson8Desc,
            MIN(IF(lesson.Number = 8, attendance.AttendanceCard, NULL)) AS Lesson8Card,
            MIN(IF(lesson.Number = 9, attendancetype.Id, NULL)) AS Lesson9AT,
            MIN(IF(lesson.Number = 9, attendancetype.Points, NULL)) AS Lesson9ATP,
            SUM(IF(lesson.Number = 9, activity.ActivityPoints, NULL)) AS Lesson9AP,
            GROUP_CONCAT(IF(lesson.Number = 9, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            SEPARATOR " + ") AS Lesson9Desc,
            MIN(IF(lesson.Number = 9, attendance.AttendanceCard, NULL)) AS Lesson9Card,
            MIN(IF(lesson.Number = 10, attendancetype.Id, NULL)) AS Lesson10AT,
            MIN(IF(lesson.Number = 10, attendancetype.Points, NULL)) AS Lesson10ATP,
            SUM(IF(lesson.Number = 10, activity.ActivityPoints, NULL)) AS Lesson10AP,
            GROUP_CONCAT(IF(lesson.Number = 10, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            SEPARATOR " + ") AS Lesson10Desc,
            MIN(IF(lesson.Number = 10, attendance.AttendanceCard, NULL)) AS Lesson10Card,
            MIN(IF(lesson.Number = 11, attendancetype.Id, NULL)) AS Lesson11AT,
            MIN(IF(lesson.Number = 11, attendancetype.Points, NULL)) AS Lesson11ATP,
            SUM(IF(lesson.Number = 11, activity.ActivityPoints, NULL)) AS Lesson11AP,
            GROUP_CONCAT(IF(lesson.Number = 11, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            SEPARATOR " + ") AS Lesson11Desc,
            MIN(IF(lesson.Number = 11, attendance.AttendanceCard, NULL)) AS Lesson11Card,
            MIN(IF(lesson.Number = 12, attendancetype.Id, NULL)) AS Lesson12AT,
            MIN(IF(lesson.Number = 12, attendancetype.Points, NULL)) AS Lesson12ATP,
            SUM(IF(lesson.Number = 12, activity.ActivityPoints, NULL)) AS Lesson12AP,
            GROUP_CONCAT(IF(lesson.Number = 12, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            SEPARATOR " + ") AS Lesson12Desc,
            MIN(IF(lesson.Number = 12, attendance.AttendanceCard, NULL)) AS Lesson12Card,
            MIN(IF(lesson.Number = 13, attendancetype.Id, NULL)) AS Lesson13AT,
            MIN(IF(lesson.Number = 13, attendancetype.Points, NULL)) AS Lesson13ATP,
            SUM(IF(lesson.Number = 13, activity.ActivityPoints, NULL)) AS Lesson13AP,
            GROUP_CONCAT(IF(lesson.Number = 13, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            SEPARATOR " + ") AS Lesson13Desc,
            MIN(IF(lesson.Number = 13, attendance.AttendanceCard, NULL)) AS Lesson13Card,
            MIN(IF(lesson.Number = 14, attendancetype.Id, NULL)) AS Lesson14AT,
            MIN(IF(lesson.Number = 14, attendancetype.Points, NULL)) AS Lesson14ATP,
            SUM(IF(lesson.Number = 14, activity.ActivityPoints, NULL)) AS Lesson14AP,
            GROUP_CONCAT(IF(lesson.Number = 14, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            SEPARATOR " + ") AS Lesson14Desc,
            MIN(IF(lesson.Number = 14, attendance.AttendanceCard, NULL)) AS Lesson14Card
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
            INNER JOIN lesson
            ON lesson.Id = attendance.LessonId
            INNER JOIN class
            ON class.Id = attendance.StudentClassId
            INNER JOIN user
            ON user.Id = student.UserId
            INNER JOIN house
            ON student.HouseId = house.Id
            WHERE student.ClassId = 39
            GROUP BY user.Name
            ORDER BY user.Name;',
                $classId);
    }

    public function getAttendancesBySemesterId($semesterId)
    {
        return $this->database->query('
            SELECT attendance.StudentClassId AS ClassId, attendance.LessonId, class.Name AS ClassName,
            attendance.AttendanceDate, lesson.Number AS LessonNumber, lesson.Name AS LessonName
            FROM attendance
            INNER JOIN class
            ON attendance.StudentClassId = class.Id
            INNER JOIN lesson
            ON attendance.LessonId = lesson.Id
            WHERE class.SemesterId = ?
            GROUP BY attendance.StudentClassId, attendance.LessonId
            ORDER BY class.Name, lesson.Number;',
                $semesterId);
    }
}