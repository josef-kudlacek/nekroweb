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