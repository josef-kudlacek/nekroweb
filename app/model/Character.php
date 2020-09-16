<?php


namespace App\Model;

use Nette;

class Character
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getCharacters()
    {
        return $this->database->query('
            SELECT *
            FROM necromancy.character
            ORDER BY Id;');
    }
}