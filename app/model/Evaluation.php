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
            FROM evaluation;');
    }

    public function getEvaluations()
    {
        $params = array(
            ['1' => 1],
        );

        return $this->getEvaluationByParams($params);
    }

    public function getStudentEvaluationStatsByClass($StudentId, $ClassId)
    {
        return $this->database->query('
            SELECT ROUND(AVG(evaluation.StarsCount), 1) AS StarsAverage,
            COUNT(evaluation.StarsCount) AS StarsCount,
            COUNT(IF(evaluation.StarsCount = 5, evaluation.StarsCount, NULL)) AS Stars5,
            COUNT(IF(evaluation.StarsCount = 4, evaluation.StarsCount, NULL)) AS Stars4,
            COUNT(IF(evaluation.StarsCount = 3, evaluation.StarsCount, NULL)) AS Stars3,
            COUNT(IF(evaluation.StarsCount = 2, evaluation.StarsCount, NULL)) AS Stars2,
            COUNT(IF(evaluation.StarsCount = 1, evaluation.StarsCount, NULL)) AS Stars1
            FROM evaluation
            INNER JOIN attendance
            ON attendance.Id = evaluation.AttendanceId
            WHERE attendance.StudentUserId = ?
            AND attendance.StudentClassId = ?;',
                $StudentId, $ClassId);
    }

    public function getStudentEvaluationsByClass($StudentId, $ClassId)
    {
        $params = array(
            ['attendance.StudentUserId' => $StudentId],
            ['attendance.StudentClassId' => $ClassId],
        );

        return $this->getEvaluationByParams($params);
    }

    public function getRemainingClassForEvaluation($StudentId, $ClassId)
    {
        return $this->database->query('
            SELECT attendance.Id, CONCAT(lesson.Number, ". ", lesson.Name) AS LessonName
            FROM attendance
            LEFT JOIN evaluation
            ON attendance.Id = evaluation.AttendanceId
            INNER JOIN lesson
            ON attendance.LessonId = lesson.Id
            WHERE attendance.StudentUserId = ?
            AND attendance.StudentClassId = ?;',
                $StudentId, $ClassId);
    }

    public function getStudentEvaluation($EvaluationId, $studentId)
    {
        return $this->database->query('
            SELECT evaluation.*
            FROM evaluation
            INNER JOIN attendance
            ON attendance.Id = evaluation.AttendanceId
            WHERE evaluation.Id = ?
            AND attendance.StudentUserId = ?;',
            $EvaluationId, $studentId);
    }

    public function insertEvaluation($values)
    {
        return $this->database->table('evaluation')->insert($values);
    }

    public function updateEvaluation($values)
    {
        try {
            return $this->database->table('evaluation')->where('Id', $values->Id)->update($values);
        } catch (Nette\Database\UniqueConstraintViolationException $uniqueConstraintViolationException) {
            throw new Nette\InvalidArgumentException();
        }
    }

    public function deleteEvaluation($EvaluationId)
    {
        return $this->database->table('evaluation')->where('Id', $EvaluationId)->delete();
    }

    private function getEvaluationByParams($params)
    {
        return $this->database->query('
            SELECT evaluation.Id, user.Name AS UserName, class.Name AS ClassName, student.HouseId,
            semester.YearFrom, semester.YearTo, evaluation.Date, evaluation.StarsCount, 
            evaluation.Description, lesson.Number AS LessonNumber, lesson.Name AS LessonName,
            attendance.AttendanceDate
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
            WHERE',
            $params,
            'ORDER BY class.SemesterId DESC, evaluation.Date DESC;');
    }

}