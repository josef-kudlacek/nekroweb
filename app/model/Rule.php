<?php


namespace App\Model;

use Nette;

class Rule
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function GetRules()
    {
        return $this->database->query('
            SELECT *
            FROM necromancy.rule rule
            ORDER BY rule.Type DESC, rule.Id;');
    }
}