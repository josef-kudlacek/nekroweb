<?php


namespace App\Model;

use Nette;

class Mark
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getMarks()
    {
        return $this->database->table('mark')->select('*');
    }

}