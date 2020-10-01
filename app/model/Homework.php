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
            SELECT assessment.Id, CONCAT(homework.Code, ' - ', assessment.Name) AS HomeworkName
            FROM semesterassesment
            INNER JOIN assessment
            ON assessment.Id = semesterassesment.AssessmentId
            INNER JOIN homework
            ON homework.AssessmentId = assessment.Id
            WHERE semesterassesment.SemesterId = ?
            ORDER BY homework.HomeworkTypeId, homework.Code;",
            $SemesterId);
    }

}