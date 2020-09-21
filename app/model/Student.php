<?php


namespace App\model;

use Nette;

class Student
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getActualStudents($semesterId)
    {
        return $this->database->query('
            SELECT user.Id AS UserId, user.Name AS UserName, user.IsActive,
            house.Id AS HouseId, student.ClassId, class.Name AS ClassName
            FROM student
            INNER JOIN user
            ON user.Id = student.UserId
            INNER JOIN class
            ON class.Id = student.ClassId
            INNER JOIN house
            ON house.Id = student.HouseId
            INNER JOIN semester
            ON semester.Id = class.SemesterId
            WHERE semester.Id = ?;',
                $semesterId);
    }

    public function deleteStudent($studentId, $classId)
    {
        return $this->database->query("
            DELETE
            FROM student
            WHERE student.UserId = ?
            AND student.ClassId = ?;",
                $studentId, $classId)->getRowCount();
    }
}