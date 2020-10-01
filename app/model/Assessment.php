<?php


namespace App\model;

use Nette;

class Assessment
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getAssessmentBySemester($SemesterId)
    {
        return $this->database->query('
            SELECT class.Id AS ClassId, user.Id AS StudentId, user.Name AS UserName, student.HouseId,
            class.Name AS ClassName, homework.Code AS HomeworkCode, assessment.Id AS AssessmentId,
            assessment.Name AS AssessmentName, mark.Value AS MarkValue, mark.Id AS MarkId, studentassessment.`Comment` AS AssessmentComment,
            studentassessment.Date AS AssessmentDate, studentassessment.ResultPoints
            FROM studentassessment
            INNER JOIN student
            ON studentassessment.StudentUserId = student.UserId
            AND studentassessment.StudentClassId = student.ClassId
            INNER JOIN user
            ON user.Id = student.UserId
            INNER JOIN class
            ON class.Id = student.ClassId
            INNER JOIN assessment
            ON assessment.Id = studentassessment.AssessmentId
            INNER JOIN mark
            ON mark.Id = studentassessment.MarkId
            INNER JOIN homework
            ON homework.AssessmentId = assessment.Id
            WHERE class.SemesterId = ?
            ORDER BY class.name, user.name, assessment.Name;',
            $SemesterId);
    }

    public function getStudentAssessment($StudentId, $ClassId, $AssessmentId)
    {
        return $this->database->query('
            SELECT class.Id AS ClassId, user.Id AS StudentId, user.Name AS UserName, student.HouseId,
            class.Name AS ClassName, homework.Code AS HomeworkCode, assessment.Id AS AssessmentId, mark.Name AS MarkName,
            assessment.Name AS AssessmentName, mark.Value AS MarkValue, mark.Id AS MarkId, studentassessment.`Comment` AS AssessmentComment,
            studentassessment.Date AS AssessmentDate, studentassessment.ResultPoints
            FROM studentassessment
            INNER JOIN student
            ON studentassessment.StudentUserId = student.UserId
            AND studentassessment.StudentClassId = student.ClassId
            INNER JOIN user
            ON user.Id = student.UserId
            INNER JOIN class
            ON class.Id = student.ClassId
            INNER JOIN assessment
            ON assessment.Id = studentassessment.AssessmentId
            INNER JOIN mark
            ON mark.Id = studentassessment.MarkId
            INNER JOIN homework
            ON homework.AssessmentId = assessment.Id
            WHERE student.UserId = ?
            AND student.ClassId = ?
            AND studentassessment.AssessmentId = ?
            ORDER BY class.name, user.name, assessment.Name;',
            $StudentId, $ClassId, $AssessmentId);
    }

    public function insertAssessment($values)
    {
        return $this->database->table('studentassessment')->insert($values);
    }

    public function deleteAssessment($StudentId, $ClassId, $AssessmentId)
    {
        return $this->database->query('
            DELETE
            FROM studentassessment
            WHERE studentassessment.StudentUserId = ?
            AND studentassessment.StudentClassId = ?
            AND studentassessment.AssessmentId = ?;',
            $StudentId, $ClassId, $AssessmentId);
    }
}