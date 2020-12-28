<?php


namespace App\Model;

use Nette;

class About
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getHistory()
    {
        $params = array(
            ['1' => 1],
        );

        return $this->getHistoryByParams($params);
    }

    public function getHistoryBySemester($semesterFrom, $semesterTo)
    {
        $semesterTo = isset($semesterTo) ? $semesterTo : $semesterFrom;

        $params = array(
            'sem.YearFrom <=' => $semesterFrom,
            $this->database::literal('?or', [
                'sem.YearTo <=' => $semesterTo,
                [$this->database::literal('sem.YearTo IS NULL')]
            ]),
        );

        return $this->getHistoryByParams($params);
    }

    private function getHistoryByParams($params)
    {
        return $this->database->query('
            SELECT sem.YearFrom, sem.YearTo, hist.Description
            FROM history hist
            INNER JOIN semester sem
            ON hist.SemesterId = sem.Id
            WHERE',
            $params,
            'ORDER BY sem.YearFrom, sem.YearTo;');
    }
}