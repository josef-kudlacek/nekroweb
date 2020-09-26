<?php


namespace App\model;

use Nette;

class Lesson
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getLessonsByYear($YearId)
    {
        return $this->database->query('
            SELECT Id, Number, Name
            FROM lesson            
            WHERE lesson.Year = ?
            ORDER BY lesson.Number;',
            $YearId);
    }

}