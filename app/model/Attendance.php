<?php


namespace App\Model;

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
            SELECT attendance.Id, house.Id AS HouseId, user.Name AS StudentName,
            attendance.AttendanceTypeId, attendancetype.Name AS AttendanceName, 
            lesson.Number AS LessonNumber, lesson.Name AS LessonName, attendance.AttendanceDate,
            SUM(ActivityPoints) AS ActivityOverall, 
            GROUP_CONCAT(CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(", activitytype.Name, ")"))
            ORDER BY activitytype.Name SEPARATOR " + ") AS ActivityDescription,
            attendancetype.Points AS ActivityPoints
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
            GROUP BY user.Name, lesson.Number
            ORDER BY lesson.Number;',
                $userId, $classId);
    }

    public function GetAttendanceById($attendanceId)
    {
        return $this->database->query('
            SELECT attendance.Id AS AttendanceId, user.Id AS UserId, user.Name AS UserName, student.HouseId AS HouseId,
            attendancetype.Id AS AttendanceTypeId, attendancetype.Name AS AttendanceName, lesson.Id AS LessonId,
            attendance.AttendanceDate, class.Id As ClassId,
            class.Name AS ClassName, semester.YearFrom, semester.YearTo, lesson.Number AS LessonNumber, lesson.Name AS LessonName
            FROM attendance
            INNER JOIN user
            ON attendance.StudentUserId = user.Id
            INNER JOIN student
            ON student.UserId = attendance.StudentUserId
            AND student.ClassId = attendance.StudentClassId
            INNER JOIN class
            ON attendance.StudentClassId = class.Id
            INNER JOIN lesson
            ON attendance.LessonId = lesson.Id
            INNER JOIN attendancetype
            ON attendance.AttendanceTypeId = attendancetype.Id
            INNER JOIN semester
            ON semester.Id = class.SemesterId
            WHERE attendance.Id = ?;',
            $attendanceId);
    }

    public function getAttendanceByClass($classId)
    {
        $params = array(
            ['student.ClassId' => $classId],
        );

        return $this->getAttendanceByParams($params);
    }

    public function getAttendanceBySemester($semesterId)
    {
        $params = array(
            ['class.SemesterId' => $semesterId],
        );

        return $this->getAttendanceByParams($params);
    }

    public function getAttendancesBySemesterId($semesterId)
    {
        return $this->database->query('
            SELECT attendance.StudentClassId AS ClassId, attendance.LessonId, class.Name AS ClassName,
            attendance.AttendanceDate, lesson.Number AS LessonNumber, lesson.Name AS LessonName, arc.FileName
            FROM attendance
            INNER JOIN class
            ON attendance.StudentClassId = class.Id
            INNER JOIN lesson
            ON attendance.LessonId = lesson.Id
            LEFT JOIN arc
            ON arc.ClassId = attendance.StudentClassId
            AND arc.LessonId = attendance.LessonId
            WHERE class.SemesterId = ?
            GROUP BY attendance.StudentClassId, attendance.LessonId
            ORDER BY class.Name, lesson.Number;',
                $semesterId);
    }

    public function getClassAttendanceSummary($classId, $lessonId)
    {
        return $this->database->query('
            SELECT DISTINCT attendance.Id AS AttendanceId,
            attendance.StudentClassId AS ClassId, attendance.LessonId AS LessonId,
            house.Id AS HouseId, user.Id AS StudentId, user.Name AS StudentName,            
            attendancetype.Id AS AttendanceTypeId,
            attendancetype.Name AS AttendanceTypeName,
            attendancetype.Points AS AttendanceTypePoints,
            SUM(activity.ActivityPoints) AS ActivityPoints,
            GROUP_CONCAT(CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")"))
            ORDER BY activitytype.Name SEPARATOR " + ") AS ActivityDescription
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
            WHERE student.ClassId = ?
            AND attendance.LessonId = ?
            GROUP BY user.Name
            ORDER BY user.Name;',
            $classId, $lessonId);
    }

    public function GetAttendancesByClassAndLesson($classId, $lessonId)
    {
        return $this->database->query('
            SELECT attendance.Id AS AttendanceId, lesson.Id AS LessonId, lesson.Name AS LessonName, attendance.AttendanceDate,
            attendance.StudentUserId, user.Name AS StudentName, student.HouseId,
            attendancetype.Id AS AttendanceTypeId, attendancetype.Name AS AttendanceType
            FROM attendance
            INNER JOIN user
            ON user.Id = attendance.StudentUserId
            INNER JOIN lesson
            ON lesson.Id = attendance.LessonId
            INNER JOIN attendancetype
            ON attendance.AttendanceTypeId = attendancetype.Id
            INNER JOIN student
            ON attendance.StudentUserId = student.UserId
            AND attendance.StudentClassId = student.ClassId
            WHERE attendance.StudentClassId = ?
            AND attendance.LessonId = ?
            ORDER BY user.name;',
            $classId, $lessonId);
    }

    public function insertAttendances($values)
    {
        return $this->database->query('INSERT INTO attendance', $values);
    }

    public function updateAttendances($values)
    {
        foreach ($values as $key => $value)
        {
            $this->database->table('attendance')->where('StudentUserId = ? AND StudentClassId = ? AND LessonId = ?',
                $values[$key]['StudentUserId'],
                $values[$key]['StudentClassId'],
                $values[$key]['LessonId'])
                ->update($values[$key]);
        }
    }

    public function updateAttendance($values, $attendanceId)
    {
        foreach ($values as $key => $value)
        {
        $this->database->table('attendance')->where('Id = ?',
            $attendanceId)
            ->update($values[$key]);
        }
    }

    public function deleteAttendances($ClassId, $LessonId)
    {
        return $this->database->query('
            DELETE activity.*, attendance.*
            FROM attendance
            LEFT JOIN activity
            ON attendance.Id = activity.AttendanceId
            WHERE attendance.StudentClassId = ?
            AND attendance.LessonId = ?;',
            $ClassId, $LessonId);
    }

    public function excuseStudent($AttendanceId)
    {
        return $this->database->query('
            UPDATE attendance
            SET AttendanceTypeId = 3
            WHERE Id = ?;',
            $AttendanceId);
    }

    private function getAttendanceByParams($params)
    {
        return $this->database->query('
            SELECT DISTINCT house.Id AS HouseId, user.Id, user.Name AS StudentName, class.Name AS ClassName,
            MIN(IF(lesson.Number = 1, attendancetype.Id, NULL)) AS Lesson1AT,
            MIN(IF(lesson.Number = 1, attendancetype.Points, NULL)) AS Lesson1ATP,
            SUM(IF(lesson.Number = 1, activity.ActivityPoints, NULL)) AS Lesson1AP,
            GROUP_CONCAT(IF(lesson.Number = 1, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            ORDER BY activitytype.Name SEPARATOR " + ") AS Lesson1Desc,
            MIN(IF(lesson.Number = 2, attendancetype.Id, NULL)) AS Lesson2AT,
            MIN(IF(lesson.Number = 2, attendancetype.Points, NULL)) AS Lesson2ATP,
            SUM(IF(lesson.Number = 2, activity.ActivityPoints, NULL)) AS Lesson2AP,
            GROUP_CONCAT(IF(lesson.Number = 2, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            ORDER BY activitytype.Name SEPARATOR " + ") AS Lesson2Desc,
            MIN(IF(lesson.Number = 3, attendancetype.Id, NULL)) AS Lesson3AT,
            MIN(IF(lesson.Number = 3, attendancetype.Points, NULL)) AS Lesson3ATP,
            SUM(IF(lesson.Number = 3, activity.ActivityPoints, NULL)) AS Lesson3AP,
            GROUP_CONCAT(IF(lesson.Number = 3, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            ORDER BY activitytype.Name SEPARATOR " + ") AS Lesson3Desc,
            MIN(IF(lesson.Number = 4, attendancetype.Id, NULL)) AS Lesson4AT,
            MIN(IF(lesson.Number = 4, attendancetype.Points, NULL)) AS Lesson4ATP,
            SUM(IF(lesson.Number = 4, activity.ActivityPoints, NULL)) AS Lesson4AP,
            GROUP_CONCAT(IF(lesson.Number = 4, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            ORDER BY activitytype.Name SEPARATOR " + ") AS Lesson4Desc,
            MIN(IF(lesson.Number = 5, attendancetype.Id, NULL)) AS Lesson5AT,
            MIN(IF(lesson.Number = 5, attendancetype.Points, NULL)) AS Lesson5ATP,
            SUM(IF(lesson.Number = 5, activity.ActivityPoints, NULL)) AS Lesson5AP,
            GROUP_CONCAT(IF(lesson.Number = 5, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            ORDER BY activitytype.Name SEPARATOR " + ") AS Lesson5Desc,
            MIN(IF(lesson.Number = 6, attendancetype.Id, NULL)) AS Lesson6AT,
            MIN(IF(lesson.Number = 6, attendancetype.Points, NULL)) AS Lesson6ATP,
            SUM(IF(lesson.Number = 6, activity.ActivityPoints, NULL)) AS Lesson6AP,
            GROUP_CONCAT(IF(lesson.Number = 6, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            ORDER BY activitytype.Name SEPARATOR " + ") AS Lesson6Desc,
            MIN(IF(lesson.Number = 7, attendancetype.Id, NULL)) AS Lesson7AT,
            MIN(IF(lesson.Number = 7, attendancetype.Points, NULL)) AS Lesson7ATP,
            SUM(IF(lesson.Number = 7, activity.ActivityPoints, NULL)) AS Lesson7AP,
            GROUP_CONCAT(IF(lesson.Number = 7, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            ORDER BY activitytype.Name SEPARATOR " + ") AS Lesson7Desc,
            MIN(IF(lesson.Number = 8, attendancetype.Id, NULL)) AS Lesson8AT,
            MIN(IF(lesson.Number = 8, attendancetype.Points, NULL)) AS Lesson8ATP,
            SUM(IF(lesson.Number = 8, activity.ActivityPoints, NULL)) AS Lesson8AP,
            GROUP_CONCAT(IF(lesson.Number = 8, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            ORDER BY activitytype.Name SEPARATOR " + ") AS Lesson8Desc,
            MIN(IF(lesson.Number = 9, attendancetype.Id, NULL)) AS Lesson9AT,
            MIN(IF(lesson.Number = 9, attendancetype.Points, NULL)) AS Lesson9ATP,
            SUM(IF(lesson.Number = 9, activity.ActivityPoints, NULL)) AS Lesson9AP,
            GROUP_CONCAT(IF(lesson.Number = 9, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            ORDER BY activitytype.Name SEPARATOR " + ") AS Lesson9Desc,
            MIN(IF(lesson.Number = 10, attendancetype.Id, NULL)) AS Lesson10AT,
            MIN(IF(lesson.Number = 10, attendancetype.Points, NULL)) AS Lesson10ATP,
            SUM(IF(lesson.Number = 10, activity.ActivityPoints, NULL)) AS Lesson10AP,
            GROUP_CONCAT(IF(lesson.Number = 10, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            ORDER BY activitytype.Name SEPARATOR " + ") AS Lesson10Desc,
            MIN(IF(lesson.Number = 11, attendancetype.Id, NULL)) AS Lesson11AT,
            MIN(IF(lesson.Number = 11, attendancetype.Points, NULL)) AS Lesson11ATP,
            SUM(IF(lesson.Number = 11, activity.ActivityPoints, NULL)) AS Lesson11AP,
            GROUP_CONCAT(IF(lesson.Number = 11, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            ORDER BY activitytype.Name SEPARATOR " + ") AS Lesson11Desc,
            MIN(IF(lesson.Number = 12, attendancetype.Id, NULL)) AS Lesson12AT,
            MIN(IF(lesson.Number = 12, attendancetype.Points, NULL)) AS Lesson12ATP,
            SUM(IF(lesson.Number = 12, activity.ActivityPoints, NULL)) AS Lesson12AP,
            GROUP_CONCAT(IF(lesson.Number = 12, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            ORDER BY activitytype.Name SEPARATOR " + ") AS Lesson12Desc,
            MIN(IF(lesson.Number = 13, attendancetype.Id, NULL)) AS Lesson13AT,
            MIN(IF(lesson.Number = 13, attendancetype.Points, NULL)) AS Lesson13ATP,
            SUM(IF(lesson.Number = 13, activity.ActivityPoints, NULL)) AS Lesson13AP,
            GROUP_CONCAT(IF(lesson.Number = 13, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            ORDER BY activitytype.Name SEPARATOR " + ") AS Lesson13Desc,
            MIN(IF(lesson.Number = 14, attendancetype.Id, NULL)) AS Lesson14AT,
            MIN(IF(lesson.Number = 14, attendancetype.Points, NULL)) AS Lesson14ATP,
            SUM(IF(lesson.Number = 14, activity.ActivityPoints, NULL)) AS Lesson14AP,
            GROUP_CONCAT(IF(lesson.Number = 14, CONCAT_WS(" ", activity.ActivityPoints, CONCAT("(",
            activitytype.Name, ")")), NULL)
            ORDER BY activitytype.Name SEPARATOR " + ") AS Lesson14Desc
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
            WHERE',
            $params,
            'GROUP BY user.Name
            ORDER BY user.Name;');
    }
}