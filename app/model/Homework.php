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
            SELECT assessment.Id, CONCAT(IFNULL(CONCAT(semesterassessment.Code, ' - '), ''), assessment.Name, ' (', class.Name, ')') AS HomeworkName
            FROM semesterassessment
            INNER JOIN assessment
                ON assessment.Id = semesterassessment.AssessmentId
            LEFT JOIN homework
                ON homework.AssessmentId = assessment.Id
            INNER JOIN class
                ON semesterassessment.ClassId = class.Id
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
            INSERT IGNORE INTO homework (AssessmentId, HomeworkTypeId)
            VALUES(
            IFNULL(?, LAST_INSERT_ID()), ?);',
            $homework->AssessmentId, $homework->HomeworkTypeId);
    }

    public function updateHomework($values)
    {
        return $this->database->table('homework')
            ->where('AssessmentId', $values['AssessmentId'])
            ->update($values);
    }

}