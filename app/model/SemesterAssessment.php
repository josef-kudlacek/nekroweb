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

    public function createRecord($semesterAssessment)
    {
        return $this->database->query('
            INSERT INTO semesterassessment (SemesterId, AssessmentId, Code)
            VALUES(
            ?, IFNULL(?, LAST_INSERT_ID()), ?);',
            $semesterAssessment->SemesterId, $semesterAssessment->AssessmentId, $semesterAssessment->Code);
    }

    public function addAssessmentToSemester($values)
    {
        return $this->database->table('semesterassessment')->insert($values);
    }

    public function removeAssessmentFromSemester($assessmentId, $semesterId)
    {
        return $this->database->table('semesterassessment')
            ->where('AssessmentId = ? AND SemesterId = ?', $assessmentId, $semesterId)
            ->delete();
    }
}