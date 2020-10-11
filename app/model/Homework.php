<?php


namespace App\Model;

use Nette;

class Homework
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getHomeworksBySemester($SemesterId)
    {
        return $this->database->query("
            SELECT assessment.Id, CONCAT(semesterassessment.Code, ' - ', assessment.Name) AS HomeworkName
            FROM semesterassessment
            INNER JOIN assessment
            ON assessment.Id = semesterassessment.AssessmentId
            INNER JOIN homework
            ON homework.AssessmentId = assessment.Id
            WHERE semesterassessment.SemesterId = ?
            ORDER BY homework.HomeworkTypeId, semesterassessment.Code;",
            $SemesterId);
    }

    public function insertHomework($values)
    {
        return $this->database->table('homework')->insert($values);
    }

    public function createRecord($homework)
    {
        return $this->database->query('
            INSERT INTO homework (AssessmentId, HomeworkTypeId, Task)
            VALUES(
            IFNULL(?, LAST_INSERT_ID()), ?, ?);',
            $homework->AssessmentId, $homework->HomeworkTypeId, $homework->Task);
    }

    public function updateHomework($values)
    {
        return $this->database->table('homework')
            ->where('AssessmentId', $values['AssessmentId'])
            ->update($values);
    }

}