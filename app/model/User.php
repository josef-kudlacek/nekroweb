<?php


namespace App\Model;

use App\MyAuthenticator;
use App\utils\Utils;
use Nette;

class User
{
    private $database;

    /** @var MyAuthenticator
     * @inject
     */
    public $authentication;

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

    public function insertStudent($values)
    {
        $this->insertUser($values);

        bdump($values);
        return $this->database->query('
            INSERT INTO student (UserId, ClassId)
            VALUES(
            LAST_INSERT_ID(), ?);',
            $values->class);
    }

}