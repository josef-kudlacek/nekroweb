<?php


namespace App\Model;

use Nette;


class CompetitionFile
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getCompetitionFileByCompetitionId($competitionId)
    {
        return $this->database->query('
            SELECT semestercompetitionfile.Id, semestercompetitionfile.CompetitionId, semestercompetitionfile.FileName
            FROM semestercompetitionfile         
            WHERE semestercompetitionfile.CompetitionId = ?
            ORDER BY semestercompetitionfile.FileName;',
                $competitionId);
    }

    public function insertCompetitionFile($values)
    {
        return $this->database->query('INSERT INTO semestercompetitionfile', $values);
    }
}