<?php


namespace App\Model;

use App\MyAuthenticator;
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

    public function insertStudent($values)
    {
        $this->insertUser($values);
        $this->database->query('
            SELECT @id := 
            (SELECT Id FROM user WHERE Name = ?);',
            $values->username);

        return $this->database->query('
            INSERT IGNORE INTO student (UserId, ClassId, HouseId)
            VALUES(
            @id, ?, ?);',
            $values->class, $values->house);
    }

    private function insertUser($values)
    {
        $this->checkUser($values->username, $values->email);

        $this->database->query('
            SELECT @email := ?,
            @password := ?,
            @roleId := 2;',
            $values->email, $values->password);

        return $this->database->query('
            INSERT INTO user (Name, Email, Password, RoleId)
            VALUES(
            ?, ?, ?, @roleId)
            ON DUPLICATE KEY UPDATE
            Email = @email, Password = @password, RoleId = @roleId;',
            $values->username, $values->email, $values->password);
    }

    public function getUserClasses($userId)
    {
        return $this->database->query('
            SELECT student.ClassId as ClassId, class.Name AS Name,
            semester.YearFrom, semester.YearTo
            FROM student
            INNER JOIN class
            ON student.ClassId = class.Id
            INNER JOIN semester
            ON semester.Id = class.SemesterId
            WHERE student.UserId = ?
            ORDER BY class.FirstLesson DESC;',
                $userId);
    }

    public function getUserHistory($userId)
    {
        return $this->database->query('
            SELECT semester.YearFrom, semester.YearTo, class.Name AS ClassName,
            mark.Shortcut, student.CertificateDate
            FROM student
            INNER JOIN class
            ON student.ClassId = class.Id
            INNER JOIN semester
            ON class.SemesterId = semester.Id
            LEFT JOIN mark
            ON student.Certificate = mark.Id
            WHERE student.UserId = ?
            ORDER BY class.FirstLesson;',
            $userId);
    }

    public function deleteUser($studentId)
    {
        return $this->database->query('
            UPDATE user
            SET Password = NULL,
            Email = NULL,
            IsActive = 0
            WHERE Id = ?;',
                $studentId);
    }

    public function activeUser($studentId)
    {
        return $this->database->query('
            UPDATE user
            SET IsActive = 1
            WHERE Id = ?;',
            $studentId);
    }

    private function checkUser($username, $email)
    {
        $user = $this->database->query('
            SELECT Name
            FROM user
            WHERE (Name = ?
            OR Email = ?)
            AND Password IS NOT NULL;',
            $username, $email)
            ->fetch();

        if (!is_null($user)) {
            throw new Nette\Database\UniqueConstraintViolationException('User exists.');
        }
    }

}