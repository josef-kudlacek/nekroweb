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
        $params = array(
            ['class.Id' => $studyClassId],
        );

        return $this->getClassByParams($params);
    }

    public function getStudentClassById($studyClassId, $studentId)
    {
        $params = array(
            ['class.Id' => $studyClassId],
            ['student.UserId' => $studentId],
        );

        return $this->getClassByParams($params);
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

    public function getPointsSumByClass($studyClassId)
    {
        $params = array(
            ['student.ClassId' => $studyClassId],
        );

        return $this->getPointsSumByParams($params);
    }

    public function getPointsSumBySemesterId($semesterId)
    {
        $params = array(
            ['class.SemesterId' => $semesterId],
        );

        return $this->getPointsSumByParams($params);
    }

    public function getOverviewBySemester($semesterId)
    {
        $params = array(
            ['class.SemesterId' => $semesterId],
            ['student.IsActive' => 1],
        );

        return $this->getOverviewByParams($params);
    }

    public function getOverviewByStudent($studentId, $classId)
    {
        $params = array(
            ['student.UserId' => $studentId],
            ['student.ClassId' => $classId],
        );

        return $this->getOverviewByParams($params);
    }

    private function getClassByParams($params)
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
            WHERE',
            $params,
            'GROUP BY class.Id;');
    }

    private function getOverviewByParams($params)
    {
        return $this->database->query('
            SELECT T.StudentId, T.StudentName, T.ClassId, T.HouseId,
            T.ClassName, T.Name, T.CertificateDate,
            IF(T.AttendanceAll IS NULL, NULL, T.AttendancesCount/T.AttendanceAll) AS Attendance,
            SUM(IF(T.Weight IS NULL, NULL, T.Weight)) AS MarkCounts,
            SUM(IF(T.Marks IS NULL, NULL, T.Marks*T.Weight)) AS Mark
            FROM
            (
            SELECT user.Id AS StudentId, user.Name AS StudentName, class.Id AS ClassId, student.HouseId,
            class.Name AS ClassName, certificateMark.Name, student.CertificateDate,
            SUM(CASE WHEN attendancetype.Points >= 5 THEN 1 ELSE 0 END) AS AttendancesCount,
            COUNT(IFNULL(attendancetype.Points, 0)) AS AttendanceAll,
            IFNULL(mark.Value, NULL) AS Marks,
            IFNULL(assessment.Weight, NULL) AS Weight
            FROM student
            INNER JOIN class
            ON student.ClassId = class.Id
            INNER JOIN user
            ON user.Id = student.UserId
            LEFT JOIN attendance
            ON attendance.StudentUserId = student.UserId
            AND attendance.StudentClassId = student.ClassId
            AND attendance.AttendanceTypeId != 5
            LEFT JOIN attendancetype
            ON attendancetype.Id = attendance.AttendanceTypeId
            LEFT JOIN mark certificateMark
            ON student.Certificate = certificateMark.Id
            LEFT JOIN studentassessment
            ON studentassessment.StudentUserId = student.UserId
            AND studentassessment.StudentClassId = student.ClassId
            LEFT JOIN mark
            ON studentassessment.MarkId = mark.Id
            LEFT JOIN assessment
            ON assessment.Id = studentassessment.AssessmentId
            WHERE',
            $params,
            'GROUP BY user.id, assessment.Id
            ORDER BY ClassName, StudentName
            ) AS T
            GROUP BY T.StudentId
            ORDER BY T.ClassName, T.StudentName;');
    }

    public function getPointsSumByParams($params)
    {
        return $this->database->query('
            	SELECT T1.ClassName, T1.Name AS StudentName, T1.HouseId, T2.AttendancePoints,
            	T1.ActivityPoints, T3.MarkPoints
                FROM
                (
                SELECT class.Name AS ClassName, user.Name, student.HouseId, 
                IFNULL(SUM(activity.ActivityPoints), 0) AS ActivityPoints
                FROM student
                INNER JOIN class
                ON student.ClassId = class.Id
                INNER JOIN user
                ON student.UserId = user.Id
                LEFT JOIN attendance
                ON student.UserId = attendance.StudentUserId
                AND student.ClassId = attendance.StudentClassId
                AND attendance.AttendanceTypeId != 5
                LEFT JOIN attendancetype
                ON attendance.AttendanceTypeId = attendancetype.Id
                LEFT JOIN activity
                ON activity.AttendanceId = attendance.Id
                WHERE',
                    $params,
                'GROUP BY student.UserId
                ORDER BY class.Name, user.Name
                ) as T1, 
                (
                SELECT user.Name, IFNULL(SUM(attendancetype.Points), 0) AS AttendancePoints
                FROM student
                INNER JOIN class
                ON student.ClassId = class.Id
                INNER JOIN user
                ON student.UserId = user.Id
                LEFT JOIN attendance
                ON student.UserId = attendance.StudentUserId
                AND student.ClassId = attendance.StudentClassId
                LEFT JOIN attendancetype
                ON attendance.AttendanceTypeId = attendancetype.Id
                WHERE',
                    $params,
                'GROUP BY student.UserId
                ORDER BY class.Name, user.Name
                ) as T2,
                (
                SELECT user.Name, IFNULL(SUM(mark.Value), 0) + IFNULL(SUM(studentassessment.AdditionalPoints), 0) AS MarkPoints
                FROM student
                INNER JOIN user                
                ON student.UserId = user.Id
                INNER JOIN class
                ON student.ClassId = class.Id
                LEFT JOIN studentassessment
                ON student.UserId = studentassessment.StudentUserId
                AND student.ClassId = studentassessment.StudentClassId
                LEFT JOIN mark
                ON mark.Id = studentassessment.MarkId
                WHERE',
                    $params,
                'GROUP BY student.UserId
                ORDER BY class.Name, user.Name
                ) as T3
                WHERE T1.Name = T2.Name
                AND T2.Name = T3.Name;
                ');
    }
}