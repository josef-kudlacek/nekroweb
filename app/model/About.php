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
        return $this->database->query('
            SELECT sem.YearFrom, sem.YearTo, hist.Description
            FROM history hist
            INNER JOIN semester sem
            ON hist.SemesterId = sem.Id
            ORDER BY sem.YearFrom, sem.YearTo;');
    }
}