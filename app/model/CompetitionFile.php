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
        $params = array(
            ['semestercompetitionfile.CompetitionId' => $competitionId],
        );

        return $this->getCompetitionFileByParams($params);
    }

    public function getCompetitionFileById($competitionFileId)
    {
        $params = array(
            ['semestercompetitionfile.Id' => $competitionFileId],
        );

        return $this->getCompetitionFileByParams($params);
    }

    public function insertCompetitionFile($values)
    {
        return $this->database->query('INSERT INTO semestercompetitionfile', $values);
    }

    public function deleteCompetitionFile($competitionFileId)
    {
        return $this->database->table('semestercompetitionfile')->where('Id', $competitionFileId)->delete();
    }

    private function getCompetitionFileByParams($params)
    {
        return $this->database->query('
            SELECT semestercompetitionfile.Id, semestercompetitionfile.CompetitionId, semestercompetitionfile.FileName
            FROM semestercompetitionfile         
            WHERE',
            $params,
            'ORDER BY semestercompetitionfile.FileName;',
            );
    }
}