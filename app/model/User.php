<?php


namespace App\Model;

use Nette;

class User
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function insertUser($values)
    {
        return $this->database->query('
            INSERT INTO user (Name, Email, Password, RoleId)
            VALUES(
            ?, ?, ?, ?);',
            $values->username, $values->email, $values->password, 2);
    }
}