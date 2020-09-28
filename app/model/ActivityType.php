<?php


namespace App\Model;

use Nette;

class ActivityType
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getActivityTypes()
    {
        return $this->database->query('
            SELECT Id, Name, Label
            FROM activitytype;');
    }

}