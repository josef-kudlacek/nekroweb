<?php


namespace App\Model;

use Nette;

class Competition
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getCompetitionById($competitionId)
    {
        $params = array(
            ['semestercompetition.Id' => $competitionId],
        );

        return $this->getCompetitionByParams($params);
    }

    public function getCompetitionBySemester($semesterId)
    {
        $params = array(
            ['semestercompetition.SemesterId' => $semesterId],
        );

        return $this->getCompetitionByParams($params);
    }

    public function getCompetitionByClassId($classId)
    {
        $params = array(
            ['class.Id' => $classId],
            [$this->database::literal('semestercompetition.CompetitionDate < NOW() + 1')],
        );

        return $this->getCompetitionByParams($params);
    }

    public function getCompetitionByIdAndClass($competitionId, $classId)
    {
        $params = array(
            ['semestercompetition.Id' => $competitionId],
            ['class.Id' => $classId],
            [$this->database::literal('semestercompetition.CompetitionDate < NOW() + 1')],
        );

        return $this->getCompetitionByParams($params);
    }

    public function insertCompetition($values)
    {
        return $this->database->table('semestercompetition')->insert($values);
    }

    public function updateCompetition($values)
    {
        return $this->database->table('semestercompetition')->where('Id', $values['Id'])->update($values);
    }

    private function getCompetitionByParams($params)
    {
        return $this->database->query('
            SELECT semestercompetition.Id, class.Name AS ClassName, class.Id AS ClassId, semestercompetition.CompetitionNumber,
            semestercompetition.CompetitionName, semestercompetition.CompetitionDate, semestercompetition.CompetitionTask
            FROM semestercompetition
            INNER JOIN class
            ON class.Id = semestercompetition.ClassId            
            WHERE',
            $params,
            'ORDER BY class.Name, semestercompetition.CompetitionNumber;');
    }


}