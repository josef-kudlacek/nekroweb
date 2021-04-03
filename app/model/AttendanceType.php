<?php


namespace App\Model;

use Nette;

class AttendanceType
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getAttendanceTypes()
    {
        return $this->database->query('
            SELECT Id, Name, Points
            FROM attendancetype Type
            ORDER BY Type.Points DESC, Type.Id;');
    }

}