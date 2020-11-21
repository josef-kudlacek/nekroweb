<?php


namespace App\Model;

use Nette;

class Quote
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getQuotes()
    {
        return $this->database->table('quote')->select('*');
    }

}