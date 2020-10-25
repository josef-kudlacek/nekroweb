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

    public function getSemesterAssessmentById($semesterAssessmentId)
    {
        return $this->database->query('
            SELECT assessment.Id AS AssessmentId, assessment.Name, assessment.Weight, year.Number, year.CodeName, 
            year.Id AS YearId, homework.HomeworkTypeId, semesterassessment.Task, semesterassessment.Code, 
            homeworktype.Name AS HomeWorkTypeName, semesterassessment.SemesterId, semesterassessment.Id,
            semesterassessment.ClassId
            FROM assessment
            INNER JOIN year
            ON assessment.YearId = year.Id
            LEFT JOIN homework
            ON assessment.Id = homework.AssessmentId
            LEFT JOIN homeworktype
            ON homework.HomeworkTypeId = homeworktype.Id
            LEFT JOIN semesterassessment
            ON assessment.Id = semesterassessment.AssessmentId
            INNER JOIN class
            ON class.YearId = year.Id
            AND class.SemesterId = semesterassessment.SemesterId
            LEFT JOIN class sc
            ON sc.Id = semesterassessment.ClassId
            WHERE semesterassessment.Id = ?
            ORDER BY assessment.YearId, assessment.Weight DESC, homework.HomeworkTypeId, semesterassessment.Code;
                ', $semesterAssessmentId);
    }

    public function getAssessmentsBySemester($semesterId)
    {
        return $this->database->query('
            SELECT assessment.Id AS AssessmentId, assessment.Name AS AssessmentName, assessment.Weight AS
            AssessmentWeight, class.Name AS ClassName, year.Number, year.CodeName, class.Id AS ClassId,
            semesterassessment.Code AS HomeworkCode, homeworktype.Name AS HomeworkTypeName, 
            semesterassessment.SemesterId, semesterassessment.Id
            FROM assessment
            INNER JOIN class
            ON class.YearId = assessment.YearId
            INNER JOIN year
            ON assessment.YearId = year.Id
            LEFT JOIN homework
            ON assessment.Id = homework.AssessmentId
            LEFT JOIN homeworktype
            ON homework.HomeworkTypeId = homeworktype.Id
            LEFT JOIN semesterassessment
            ON semesterassessment.AssessmentId = assessment.Id
            AND semesterassessment.ClassId = class.Id
            AND semesterassessment.SemesterId = class.SemesterId
            WHERE class.SemesterId = ?
            ORDER BY assessment.YearId, assessment.Weight DESC, homework.HomeworkTypeId,
            semesterassessment.Code;',
            $semesterId);
    }

    public function getAssessmentBySemester($SemesterId)
    {
        return $this->database->query('
            SELECT studentassessment.Id AS StudentAssessmentId, class.Id AS ClassId, user.Id AS StudentId,
            user.Name AS UserName, student.HouseId, class.Name AS ClassName, semesterassessment.Code AS
            HomeworkCode,
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
            ON class.Id = studentassessment.StudentClassId
            INNER JOIN assessment
            ON assessment.Id = studentassessment.AssessmentId
            INNER JOIN mark
            ON mark.Id = studentassessment.MarkId
            LEFT JOIN homework
            ON homework.AssessmentId = assessment.Id
            INNER JOIN semesterassessment
            ON semesterassessment.AssessmentId = studentassessment.AssessmentId
            AND semesterassessment.ClassId = studentassessment.StudentClassId
            AND semesterassessment.SemesterId = class.SemesterId
            WHERE class.SemesterId = ?
            GROUP BY studentassessment.Id
            ORDER BY class.name, user.name, assessment.Weight DESC, HomeworkCode;',
            $SemesterId);
    }

    public function getStudentAssessment($StudentAssessmentId)
    {
        return $this->database->query('
            SELECT studentassessment.Id, class.Id AS ClassId, user.Id AS StudentUserId,
            user.Name AS UserName, student.HouseId, class.Name AS ClassName, semesterassessment.Code AS HomeworkCode,
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
            INNER JOIN semesterassessment
            ON assessment.Id = semesterassessment.AssessmentId
            AND semesterassessment.SemesterId = class.SemesterId
            WHERE studentassessment.Id = ?
            ORDER BY class.name, user.name, assessment.Name;',
            $StudentAssessmentId);
    }

    public function getAssessmentById($assessmentId)
    {
        return $this->database->query('
            SELECT *
            FROM assessment
            LEFT JOIN homework
            ON homework.AssessmentId = assessment.Id
            WHERE Id = ?;',
            $assessmentId);
    }

    public function insertAssessment($values)
    {
        return $this->database->table('assessment')->insert($values);
    }

    public function updateAssessment($values)
    {
        return $this->database->table('assessment')->where('Id', $values['Id'])->update($values);
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