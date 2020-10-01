<?php


namespace App\Model;

use Nette;

class HomeworkType
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getHomeworkTypes()
    {
        return $this->database
            ->table('homeworktype')
            ->select('*');
    }

}