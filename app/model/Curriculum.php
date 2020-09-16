<?php


namespace App\Model;

use Nette;

class Curriculum
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getLessons()
    {
        return $this->database->query('
            SELECT lesson.Id, year.Number AS YearNumber, year.CodeName, lesson.Number AS LessonNumber, lesson.Name 
            FROM necromancy.lesson lesson
            INNER JOIN necromancy.year year
            ON lesson.Year = year.Id
            ORDER BY lesson.Year, lesson.Number;');
    }

    public function getLessonYears()
    {
        return $this->database->query('
            SELECT *
            FROM necromancy.year year;');
    }
}