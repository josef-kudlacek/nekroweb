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
        $params = array(
            ['1' => 1],
        );

        return $this->getCharactersByParams($params);
    }

    public function getCharactersByClassId($classId)
    {
        $params = array(
            [$this->database::literal('year.Number
                <=
                (
                SELECT year.Number
                FROM year
                INNER JOIN class
                	ON year.Id = class.YearId
                WHERE class.Id = ?
                )', $classId)],
        );

        return $this->getCharactersByParams($params);
    }

    private function getCharactersByParams($params)
    {
        return $this->database->query('
            SELECT necromancy.character.*
            FROM necromancy.character
            INNER JOIN year
	        ON necromancy.character.YearId = year.Id
            WHERE',
            $params,
            'ORDER BY Id;');
    }
}