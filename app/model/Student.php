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
            LEFT JOIN house
            ON house.Id = student.HouseId
            INNER JOIN semester
            ON semester.Id = class.SemesterId
            WHERE semester.Id = ?
            ORDER BY class.Name, user.Name;',
                $semesterId);
    }

    public function getStudent($studentId, $classId)
    {
        return $this->database->query("
            SELECT user.Id AS UserId, user.name AS UserName, user.IsActive, user.Email,
            student.HouseId, house.Name AS HouseName, 
            student.ClassId, class.Name AS ClassName
            FROM student
            INNER JOIN user
            ON user.Id = student.UserId
            INNER JOIN class
            ON class.Id = student.ClassId
            LEFT JOIN house
            ON house.Id = student.HouseId
            WHERE student.UserId = ?
            AND student.ClassId = ?;",
            $studentId, $classId);
    }

    public function updateStudent($studentId, $values)
    {
        $this->updateStudentInfo($studentId, $values);

        try {
            return $this->database->query('
                UPDATE student
                SET ClassId = ?,
                HouseId = ?
                WHERE UserId = ?;',
                    $values->class, $values->house, $studentId)->getRowCount();
        } catch (Nette\Database\ForeignKeyConstraintViolationException $foreignKeyConstraintViolationException) {
            return -1;
        }
    }

    public function deleteStudent($studentId, $classId)
    {
        return $this->database->query("
            DELETE student.*, attendance.*, studentanswer.*, studentassessment.*
            FROM student
            LEFT JOIN attendance
            ON student.UserId = attendance.StudentUserId
            AND student.ClassId = attendance.StudentClassId
            LEFT JOIN studentanswer
            ON student.UserId = studentanswer.StudentUserId
            AND student.ClassId = studentanswer.StudentClassId
            LEFT JOIN studentassessment
            ON student.UserId = studentassessment.StudentUserId
            AND student.ClassId = studentassessment.StudentClassId
            WHERE student.UserId = ?
            AND student.ClassId = ?;",
                $studentId, $classId)->getRowCount();
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
}