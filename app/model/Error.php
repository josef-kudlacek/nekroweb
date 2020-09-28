<?php


namespace App\Model;

use Nette;

class Error
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getErrors()
    {
        return $this->database->query('
            SELECT error.Id AS ErrorId, error.Date,
            error.UserId, user.Name AS UserName, student.HouseId,
            error.FileName, error.Description, error.State, error.Reaction 
            FROM suggestionerror error
            INNER JOIN user
            ON error.UserId = user.Id
            INNER JOIN (
            SELECT * FROM
            	(
            	SELECT student.UserId, student.HouseId
            	FROM student
            	ORDER BY student.ClassId DESC
            	) x GROUP BY x.UserId
            ) student
            ON student.UserId = user.Id
            GROUP BY error.Id
            ORDER BY error.Date, error.Id;');
    }

    public function insertError($values)
    {
        return $this->database->table('suggestionerror')->insert($values);
    }
}