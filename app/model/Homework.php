<?php


namespace App\Model;

use Nette;

class Homework
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }



}