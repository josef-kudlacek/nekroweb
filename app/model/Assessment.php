<?php


namespace App\Model;

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
            SELECT studentassessment.Id AS StudentAssessmentId, class.Id AS ClassId, user.Id AS StudentId,
            user.Name AS UserName, student.HouseId, class.Name AS ClassName, homework.Code AS HomeworkCode,
            assessment.Id AS AssessmentId, assessment.Name AS AssessmentName, mark.Value AS MarkValue,
            mark.Id AS MarkId, studentassessment.`Comment` AS AssessmentComment,
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

    public function getStudentAssessment($StudentAssessmentId)
    {
        return $this->database->query('
            SELECT studentassessment.Id, class.Id AS ClassId, user.Id AS StudentUserId,
            user.Name AS UserName, student.HouseId, class.Name AS ClassName, homework.Code AS HomeworkCode,
            assessment.Id AS AssessmentId, mark.Name AS MarkName, assessment.Name AS AssessmentName,
            mark.Value AS MarkValue, mark.Id AS MarkId, studentassessment.`Comment`,
            studentassessment.Date, studentassessment.ResultPoints
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
            WHERE studentassessment.Id = ?
            ORDER BY class.name, user.name, assessment.Name;',
            $StudentAssessmentId);
    }

    public function insertAssessment($values)
    {
        return $this->database->table('studentassessment')->insert($values);
    }

    public function updateAssessment($values)
    {
        return $this->database->table('studentassessment')->where('Id', $values['Id'])->update($values);
    }

    public function deleteAssessment($StudentAssessmentId)
    {
        return $this->database->query('
            DELETE
            FROM studentassessment
            WHERE studentassessment.Id = ?;',
            $StudentAssessmentId);
    }
}