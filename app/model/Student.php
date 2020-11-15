<?php


namespace App\Model;

use Nette;

class Student
{
    private $database;

    /** @var Model\User
     * @inject
     */
    public $dbUser;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getStudentsBySemesterId($semesterId)
    {
        $params = array(
            ['semester.Id' => $semesterId],
        );

        return $this->getStudentByParams($params);
    }

    public function getActualStudents($semesterId)
    {
        $params = array(
            ['semester.Id' => $semesterId],
            ['student.IsActive' => 1],
        );

        return $this->getStudentByParams($params);
    }

    public function getStudent($studentId, $classId)
    {
        $params = array(
            ['student.UserId' => $studentId],
            ['student.ClassId' => $classId],
        );

        return $this->getStudentByParams($params);
    }

    public function getStudentsByClassId($classId)
    {
        $params = array(
            ['student.ClassId' => $classId],
            ['student.IsActive' => 1],
        );

        return $this->getStudentByParams($params);
    }

    public function getCertificationInfo($studentId, $classId)
    {
        return $this->database->query("
            SELECT *
            FROM student
            WHERE student.UserId = ?
            AND student.ClassId = ?;",
            $studentId, $classId);
    }

    public function insertStudent($values)
    {
        $this->insertUser($values->username, $values->isactive);

        $this->database->query('
            SELECT @id := 
            (SELECT Id FROM user WHERE Name = ?);',
            $values->username);

        $this->database->query('
            SELECT @houseId := ?,
            @classId := ?;',
            $values->house, $values->class);

        return $this->database->query('
            INSERT INTO student (UserId, ClassId, HouseId)
            VALUES(
            @id, @classId, @houseId)
            ON DUPLICATE KEY UPDATE
            HouseId = @houseId;');
    }

    public function updateStudent($studentId, $values)
    {
        $this->updateStudentInfo($studentId, $values);

        try {
            return $this->database->query('
                UPDATE IGNORE student
                SET ClassId = ?,
                HouseId = ?                
                WHERE UserId = ?;',
                    $values->class, $values->house, $studentId)->getRowCount();
        } catch (Nette\Database\ForeignKeyConstraintViolationException $foreignKeyConstraintViolationException) {
            return -1;
        }
    }

    public function setActive($studentId, $classId, $isActive)
    {
        return $this->database->query("
            UPDATE student
            SET IsActive = ?
            WHERE student.UserId = ?
            AND student.ClassId = ?;",
                $isActive, $studentId, $classId);
    }

    public function certificateStudent($values)
    {
        return $this->database->table('student')
            ->where('UserId = ? AND ClassId = ?', $values->UserId, $values->ClassId)
            ->update($values);
    }

    private function updateStudentInfo($studentId, $values)
    {
        return $this->database->query('
            UPDATE user
            SET Name = ?,
            Email = ?,
            IsActive = ?
            WHERE Id = ?;',
            $values->username, $values->email, $values->isactive,
            $studentId);
    }

    private function insertUser($username, $isactive)
    {
        return $this->database->query('
            INSERT INTO user (Name, RoleId, IsActive)
            VALUES(
            ?, 2, ?)
            ON DUPLICATE KEY UPDATE
            Name = Name;',
            $username, $isactive);
    }

    private function getStudentByParams($params)
    {
        return $this->database->query('
            SELECT user.Id AS UserId, user.name AS UserName, user.IsActive, user.Email,
            student.HouseId, student.ClassId, student.IsActive AS StudentIsActive,
            house.Name AS HouseName, class.Name AS ClassName
            FROM student
            INNER JOIN user
            ON user.Id = student.UserId
            INNER JOIN class
            ON class.Id = student.ClassId
            LEFT JOIN house
            ON house.Id = student.HouseId
            INNER JOIN semester
            ON semester.Id = class.SemesterId
            WHERE',
            $params,
            'ORDER BY class.Name, user.Name;');
    }
}