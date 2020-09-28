<?php


namespace App\Model;

use Nette;

class House
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getHouses()
    {
        return $this->database->query('
            SELECT house.Id, house.Name
            FROM house;');
    }

}