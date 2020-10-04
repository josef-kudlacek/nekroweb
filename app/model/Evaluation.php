<?php


namespace App\Model;

use Nette;

class Evaluation
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getEvaluationStats()
    {
        return $this->database->query('
            SELECT ROUND(AVG(evaluation.StarsCount), 1) AS StarsAverage,
            COUNT(evaluation.StarsCount) AS StarsCount,
            COUNT(IF(evaluation.StarsCount = 5, evaluation.StarsCount, NULL)) AS Stars5,
            COUNT(IF(evaluation.StarsCount = 4, evaluation.StarsCount, NULL)) AS Stars4,
            COUNT(IF(evaluation.StarsCount = 3, evaluation.StarsCount, NULL)) AS Stars3,
            COUNT(IF(evaluation.StarsCount = 2, evaluation.StarsCount, NULL)) AS Stars2,
            COUNT(IF(evaluation.StarsCount = 1, evaluation.StarsCount, NULL)) AS Stars1
            FROM evaluation;
                ');
    }

    public function getEvaluations()
    {
        return $this->database->query('
            SELECT evaluation.Id, user.Name AS UserName, class.Name AS ClassName, student.HouseId,
            semester.YearFrom, semester.YearTo, evaluation.Date, evaluation.StarsCount, 
            evaluation.Description, lesson.Number AS LessonNumber, lesson.Name AS LessonName
            FROM evaluation
            INNER JOIN attendance
            ON attendance.Id = evaluation.AttendanceId
            INNER JOIN student
            ON student.UserId = attendance.StudentUserId
            AND student.ClassId = attendance.StudentClassId
            INNER JOIN class
            ON class.Id = student.ClassId
            INNER JOIN user
            ON user.Id = student.UserId
            INNER JOIN semester
            ON semester.Id = class.SemesterId
            INNER JOIN lesson
            ON lesson.Id = attendance.LessonId
            ORDER BY class.SemesterId DESC, class.Name, lesson.Number, user.Name;
                ');
    }

}