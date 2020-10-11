<?php


namespace App\Model;

use Nette;

class StudentAssessment
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getHomeworkAssessments($SemesterId)
    {
        return $this->database->query('
            SELECT class.Id AS ClassId, user.Id AS StudentId, class.Name AS ClassName,
            semesterassessment.Code AS HomeworkCode,assessment.Name AS AssessmentName, mark.Value AS MarkValue,
            studentassessment.`Comment` AS AssessmentComment, studentassessment.Date AS AssessmentDate,
            studentassessment.ResultPoints
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
            INNER JOIN semesterassessment
            ON assessment.Id = semesterassessment.AssessmentId
            AND semesterassessment.SemesterId = class.SemesterId
            INNER JOIN mark
            ON mark.Id = studentassessment.MarkId
            INNER JOIN homework
            ON homework.AssessmentId = assessment.Id
            WHERE class.SemesterId = ?;',
            $SemesterId);
    }

    public function getStudentAssessments($StudentId, $ClassId)
    {
        return $this->database->query('
            SELECT class.Id AS ClassId, user.Id AS StudentId, class.Name AS ClassName, semesterassessment.Code AS HomeworkCode,
            assessment.Name AS AssessmentName, mark.Name AS MarkName, mark.Id AS MarkId, studentassessment.`Comment` AS AssessmentComment,
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
            INNER JOIN semesterassessment
            ON assessment.Id = semesterassessment.AssessmentId
            AND semesterassessment.SemesterId = class.SemesterId
            INNER JOIN mark
            ON mark.Id = studentassessment.MarkId
            INNER JOIN homework
            ON homework.AssessmentId = assessment.Id
            WHERE student.UserId = ?
            AND student.ClassId = ?;',
            $StudentId, $ClassId);
    }

    public function getStudentAssessmentsByClass($ClassId)
    {
        return $this->database->query('
            SELECT user.Id, user.Name AS StudentName, student.HouseId AS HouseId,
            MIN(IF(assessment.Weight = 3, mark.Shortcut, NULL)) AS ZS,
            MIN(IF(assessment.Weight = 3, mark.Id, NULL)) AS ZSid,
            MIN(IF(semesterassessment.Code = "Esej", mark.Shortcut, NULL)) AS Esej,
            MIN(IF(semesterassessment.Code = "Esej", mark.Id, NULL)) AS Esejid,
            MAX(IF(semesterassessment.Code = "1.PDÚ", mark.Shortcut, NULL)) AS PDU1,
            MAX(IF(semesterassessment.Code = "1.PDÚ", mark.Id, NULL)) AS PDU1id,
            MAX(IF(semesterassessment.Code = "2.PDÚ", mark.Shortcut, NULL)) AS PDU2,
            MAX(IF(semesterassessment.Code = "2.PDÚ", mark.Id, NULL)) AS PDU2id,
            MAX(IF(semesterassessment.Code = "1.NDÚ", mark.Shortcut, NULL)) AS NDU1,
            MAX(IF(semesterassessment.Code = "1.NDÚ", mark.Id, NULL)) AS NDU1id,
            MIN(IF(semesterassessment.Code = "2.NDÚ", mark.Shortcut, NULL)) AS NDU2,
            MAX(IF(semesterassessment.Code = "2.NDÚ", mark.Id, NULL)) AS NDU2id,
            MIN(IF(semesterassessment.Code = "3.NDÚ", mark.Shortcut, NULL)) AS NDU3,
            MAX(IF(semesterassessment.Code = "3.NDÚ", mark.Id, NULL)) AS NDU3id,
            MIN(IF(semesterassessment.Code = "4.NDÚ", mark.Shortcut, NULL)) AS NDU4,
            MAX(IF(semesterassessment.Code = "4.NDÚ", mark.Id, NULL)) AS NDU4id,
            MIN(IF(semesterassessment.Code = "5.NDÚ", mark.Shortcut, NULL)) AS NDU5,
            MAX(IF(semesterassessment.Code = "5.NDÚ", mark.Id, NULL)) AS NDU5id,
            MIN(IF(semesterassessment.Code = "6.NDÚ", mark.Shortcut, NULL)) AS NDU6,
            MAX(IF(semesterassessment.Code = "6.NDÚ", mark.Id, NULL)) AS NDU6id,
            MIN(IF(semesterassessment.Code = "7.NDÚ", mark.Shortcut, NULL)) AS NDU7,
            MAX(IF(semesterassessment.Code = "7.NDÚ", mark.Id, NULL)) AS NDU7id,
            MIN(IF(semesterassessment.Code = "8.NDÚ", mark.Shortcut, NULL)) AS NDU8,
            MAX(IF(semesterassessment.Code = "8.NDÚ", mark.Id, NULL)) AS NDU8id
            FROM student
            INNER JOIN user
            ON user.Id = student.UserId
            INNER JOIN class
            ON class.Id = student.ClassId
            LEFT JOIN studentassessment
            ON studentassessment.StudentUserId = student.UserId
            AND studentassessment.StudentClassId = student.ClassId
            LEFT JOIN homework
            ON homework.AssessmentId = studentassessment.AssessmentId
            LEFT JOIN mark
            ON mark.Id = studentassessment.MarkId
            LEFT JOIN assessment
            ON assessment.Id = studentassessment.AssessmentId
            LEFT JOIN semesterassessment
            ON assessment.Id = semesterassessment.AssessmentId
            AND semesterassessment.SemesterId = class.SemesterId
            WHERE class.Id = ?
            GROUP BY user.name
            ORDER BY user.name;',
            $ClassId);
    }

    public function insertAssessment($values)
    {
        return $this->database->table('studentassessment')->insert($values);
    }

    public function updateAssessment($values)
    {
        return $this->database->table('studentassessment')->where('Id', $values['Id'])->update($values);
    }

}