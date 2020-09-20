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
        $this->checkUser($values->username);

        $this->database->query('
            SELECT @email := ?,
            @password := ?;',
            $values->email, $values->password);

        return $this->database->query('
            INSERT INTO user (Name, Email, Password, RoleId)
            VALUES(
            ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            Email = @email, Password = @password;',
            $values->username, $values->email, $values->password, 2);
    }

    public function insertStudent($values)
    {
        $this->insertUser($values);

        return $this->database->query('
            INSERT IGNORE INTO student (UserId, ClassId)
            VALUES(
            LAST_INSERT_ID(), ?);',
            $values->class);
    }

    public function deleteUser($username)
    {
        return $this->database->query('
            UPDATE user
            SET Password = NULL,
            Email = NULL,
            IsActive = 0
            WHERE Name = ?;',
                $username);
    }

    private function checkUser($username)
    {
        $user = $this->database->query('
            SELECT Name
            FROM user
            WHERE Name = ?
            AND Email IS NOT NULL
            AND Password IS NOT NULL;',
            $username)
            ->fetch();

        if (!is_null($user)) {
            throw new Nette\Database\UniqueConstraintViolationException('User exists.');
        }
    }

}