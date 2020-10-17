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

    public function getAssessmentByIdAndSemester($AssessmentId, $SemesterId)
    {
        return $this->database->query('
            SELECT assessment.Id, assessment.Name, assessment.Weight, year.Number, year.CodeName, year.Id AS YearId,
            homework.HomeworkTypeId, homework.Task, semesterassessment.Code, homeworktype.Name AS HomeWorkTypeName, 
            semesterassessment.SemesterId
            FROM assessment
            INNER JOIN year
            ON assessment.YearId = year.Id
            LEFT JOIN homework
            ON assessment.Id = homework.AssessmentId
            LEFT JOIN homeworktype
            ON homework.HomeworkTypeId = homeworktype.Id
            LEFT JOIN semesterassessment
            ON assessment.Id = semesterassessment.AssessmentId
            AND semesterassessment.SemesterId = ?
            INNER JOIN class
            ON class.YearId = year.Id
            AND class.SemesterId = semesterassessment.SemesterId
            WHERE assessment.Id = ?
            ORDER BY assessment.YearId, assessment.Weight DESC, homework.HomeworkTypeId, semesterassessment.Code;
                ', $SemesterId, $AssessmentId);
    }

    public function getAssessmentsBySemester($semesterId)
    {
        $this->database->query('
            SELECT @SemesterId := ?;
            ', $semesterId);

        return $this->database->query('
            SELECT assessment.Id AS AssessmentId, assessment.Name AS AssessmentName, assessment.Weight AS AssessmentWeight,
            class.Name AS ClassName, year.Number, year.CodeName, semesterassessment.Code AS HomeworkCode, 
            homeworktype.Name AS HomeworkTypeName, semesterassessment.SemesterId
            FROM assessment
            INNER JOIN year
            ON assessment.YearId = year.Id
            LEFT JOIN homework
            ON assessment.Id = homework.AssessmentId
            LEFT JOIN homeworktype
            ON homework.HomeworkTypeId = homeworktype.Id
            LEFT JOIN semesterassessment
            ON assessment.Id = semesterassessment.AssessmentId
            AND semesterassessment.SemesterId = @SemesterId
            INNER JOIN class
            ON class.YearId = year.Id
            AND class.SemesterId = @SemesterId
            ORDER BY assessment.YearId, assessment.Weight DESC, homework.HomeworkTypeId, semesterassessment.Code;');
    }

    public function getAssessmentBySemester($SemesterId)
    {
        return $this->database->query('
            SELECT studentassessment.Id AS StudentAssessmentId, class.Id AS ClassId, user.Id AS StudentId,
            user.Name AS UserName, student.HouseId, class.Name AS ClassName, semesterassessment.Code AS HomeworkCode,
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
            INNER JOIN semesterassessment
            ON assessment.Id = semesterassessment.AssessmentId
            AND semesterassessment.SemesterId = class.SemesterId 
            WHERE class.SemesterId = ?
            ORDER BY class.name, user.name, assessment.Name;',
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