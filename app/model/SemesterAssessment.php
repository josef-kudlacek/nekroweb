<?php


namespace App\Model;

use Nette;

class SemesterAssessment
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getAssessmentsInSemesterBySemesterId($SemesterId)
    {
        return $this->database->query('
            SELECT semesterassessment.Code, GROUP_CONCAT(DISTINCT assessment.Name ORDER BY class.Name SEPARATOR ", ") AS Name
            FROM semesterassessment
            INNER JOIN assessment
            ON assessment.Id = semesterassessment.AssessmentId
            INNER JOIN class
            ON class.Id = semesterassessment.ClassId
            WHERE semesterassessment.SemesterId = ?
            GROUP BY semesterassessment.Code
            ORDER BY assessment.Weight DESC, semesterassessment.Code;',
                $SemesterId);
    }

    public function getAssessmentsInSemesterByClassId($ClassId)
    {
        return $this->database->query('
            SELECT semesterassessment.Code, assessment.Name
            FROM semesterassessment
            INNER JOIN assessment
            ON assessment.Id = semesterassessment.AssessmentId
            WHERE semesterassessment.ClassId = ?
            ORDER BY assessment.Weight DESC, semesterassessment.Code;',
                $ClassId);
    }

    public function createRecord($semesterAssessment)
    {
        return $this->database->query('
            INSERT INTO semesterassessment (SemesterId, ClassId, AssessmentId, Code, Task)
            VALUES(
            ?, ?, IFNULL(?, LAST_INSERT_ID()), ?);',
            $semesterAssessment->SemesterId, $semesterAssessment->ClassId, $semesterAssessment->AssessmentId,
            $semesterAssessment->Code, $semesterAssessment->Task);
    }

    public function updateRecord($values)
    {
        return $this->database->table('semesterassessment')
            ->where('Id = ?', $values['Id'])
            ->update($values);
    }

    public function addAssessmentToSemester($values)
    {
        return $this->database->table('semesterassessment')
            ->insert($values);
    }

    public function removeAssessmentFromSemester($semesterAssessment)
    {
        return $this->database->table('semesterassessment')
            ->where('Id = ?', $semesterAssessment)
            ->delete();
    }
}