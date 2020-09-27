<?php


namespace App\model;

use Nette;

class Arc
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function insertArc($values)
    {
        return $this->database->query('
            INSERT INTO arc
            VALUES (?, ?, ?);',
            $values->ClassId, $values->LessonId, $values->FileName);
    }
}