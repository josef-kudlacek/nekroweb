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

    public function getStudentAssessments($StudentId, $ClassId)
    {
        $params = array(
            ['studentassessment.StudentUserId' => $StudentId],
            ['studentassessment.StudentClassId' => $ClassId],
        );

        return $this->getStudentAssessmentsByParams($params);
    }

    public function getStudentAssessmentsByStudentClassAndAssesmentId($StudentId, $ClassId, $AssessmentId)
    {
        $params = array(
            ['studentassessment.StudentUserId' => $StudentId],
            ['studentassessment.StudentClassId' => $ClassId],
            ['studentassessment.AssessmentId' => $AssessmentId],
        );

        return $this->getStudentAssessmentsByParams($params);
    }

    public function getStudentAssessmentsByAssessment($AssessmentId)
    {
        $params = array(
            ['studentassessment.AssessmentId' => $AssessmentId],
        );

        return $this->getStudentAssessmentsByParams($params);
    }

    public function getStudentAssessmentsByFileName($FileName)
    {
        $params = array(
            ['studentassessment.FileName' => $FileName],
        );

        return $this->getStudentAssessmentsByParams($params);
    }

    public function getStudentAssessmentsByClass($ClassId)
    {
        $params = array(
            ['class.Id' => $ClassId],
        );

        return $this->getGroupedStudentAssessmentsByParams($params);
    }

    public function getStudentAssessmentsBySemester($SemesterId)
    {
        $params = array(
            ['class.SemesterId' => $SemesterId],
        );

        return $this->getGroupedStudentAssessmentsByParams($params);
    }

    public function insertStudentAssessment($values)
    {
        $this->database->query('INSERT INTO studentassessment', $values,
            'ON DUPLICATE KEY UPDATE', $values);
    }

    public function updateAssessment($values)
    {
        return $this->database->table('studentassessment')->where('Id', $values['Id'])->update($values);
    }

    public function deleteAssessmentFileName($fileName)
    {
        return $this->database->table('studentassessment')->where('FileName', $fileName)->update([
            'FileName' => NULL
        ]);
    }

    private function getGroupedStudentAssessmentsByParams($params)
    {
        return $this->database->query('
            SELECT user.Id, user.Name AS StudentName, class.Name AS ClassName, student.HouseId AS HouseId,
            MIN(IF(semesterassessment.Code = "ZS", mark.Shortcut, NULL)) AS ZS,
            MIN(IF(semesterassessment.Code = "ZS", mark.Id, NULL)) AS ZSid,
            MIN(IF(semesterassessment.Code = "ZS2", mark.Shortcut, NULL)) AS ZS2,
            MIN(IF(semesterassessment.Code = "ZS2", mark.Id, NULL)) AS ZS2id,
            MIN(IF(semesterassessment.Code = "Esej", mark.Shortcut, NULL)) AS Esej,
            MIN(IF(semesterassessment.Code = "Esej", mark.Id, NULL)) AS Esejid,
            MIN(IF(semesterassessment.Code = "Esej", studentassessment.AdditionalPoints, NULL)) AS EsejPoints,
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
            MAX(IF(semesterassessment.Code = "8.NDÚ", mark.Id, NULL)) AS NDU8id,
            MIN(IF(semesterassessment.Code = "9.NDÚ", mark.Shortcut, NULL)) AS NDU9,
            MAX(IF(semesterassessment.Code = "9.NDÚ", mark.Id, NULL)) AS NDU9id,
            MIN(IF(semesterassessment.Code = "10.NDÚ", mark.Shortcut, NULL)) AS NDU10,
            MAX(IF(semesterassessment.Code = "10.NDÚ", mark.Id, NULL)) AS NDU10id
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
            ON semesterassessment.AssessmentId = studentassessment.AssessmentId
            AND semesterassessment.ClassId = studentassessment.StudentClassId
            AND semesterassessment.SemesterId = class.SemesterId
            WHERE',
            $params,
            'GROUP BY user.name
            ORDER BY user.name;');
    }

    public function getStudentAssessmentsByParams($params)
    {
        return $this->database->query('
            SELECT class.Id AS ClassId, user.Id AS StudentId, user.Name as StudentName, class.Name AS ClassName, house.Id AS HouseId, studentassessment.FileName,
            semesterassessment.Code AS HomeworkCode, assessment.Name AS AssessmentName, mark.Name AS MarkName, mark.Id AS MarkId, mark.Value AS MarkValue,
            studentassessment.`Comment` AS AssessmentComment, studentassessment.Date AS AssessmentDate, studentassessment.AdditionalPoints, 
            semester.YearFrom, semester.YearTo, studentassessment.Id
            FROM studentassessment
            INNER JOIN student
            ON studentassessment.StudentUserId = student.UserId
            AND studentassessment.StudentClassId = student.ClassId
            INNER JOIN user
            ON user.Id = studentassessment.StudentUserId
            INNER JOIN class
            ON class.Id = studentassessment.StudentClassId
            INNER JOIN assessment
            ON assessment.Id = studentassessment.AssessmentId
            INNER JOIN semesterassessment
            ON studentassessment.AssessmentId = semesterassessment.AssessmentId
            AND class.SemesterId = semesterassessment.SemesterId
            AND class.Id = semesterassessment.ClassId
            LEFT JOIN mark
            ON mark.Id = studentassessment.MarkId
            INNER JOIN homework
            ON homework.AssessmentId = studentassessment.AssessmentId
            INNER JOIN semester
            ON class.SemesterId = semester.Id
            INNER JOIN house
            ON student.HouseId = house.Id  
            WHERE',
            $params,
            'ORDER BY semester.YearFrom, semester.YearTo, class.Name, user.Name;');
    }
}