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

    public function getUsers()
    {
        return $this->database->query('
            SELECT user.Id, user.Name
            FROM user
            ORDER BY user.Name;');
    }

    public function GetUserById($userId)
    {
        return $this->database->query('
            SELECT Id, Name, Email, IsActive
            FROM user
            WHERE Id = ?;',
            $userId);
    }

    public function getStudentSum($studentId, $classId)
    {
        $this->database->query('
            SELECT @InStudentUserId := ?,
            @InStudentClassId := ?;
            ', $studentId, $classId);

        return $this->database->query('
            	SELECT 
	            (SELECT
	            SUM(attendancetype.Points)
	            FROM attendance
	            INNER JOIN attendancetype
	            ON attendance.AttendanceTypeId = attendancetype.Id
	            WHERE attendance.StudentUserId = @InStudentUserId
	            AND attendance.StudentClassId = @InStudentClassId
	            GROUP BY attendance.StudentUserId
	            ) AS attendancePoints,
	            (
	            SELECT
	            SUM(activity.ActivityPoints)
	            FROM attendance
	            INNER JOIN attendancetype
	            ON attendance.AttendanceTypeId = attendancetype.Id
	            LEFT JOIN activity
	            ON activity.AttendanceId = attendance.Id
	            WHERE attendance.StudentUserId = @InStudentUserId
	            AND attendance.StudentClassId = @InStudentClassId
	            GROUP BY attendance.StudentUserId
	            ) AS activityPoints,
	            (
	            SELECT SUM(mark.Value) + SUM(studentassessment.AdditionalPoints)
	            FROM studentassessment
	            INNER JOIN mark
	            ON mark.Id = studentassessment.MarkId
	            WHERE studentassessment.StudentUserId = @InStudentUserId
	            AND studentassessment.StudentClassId = @InStudentClassId
	            ) AS markPoints; 
                ');
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
            AND student.IsActive = 1
            ORDER BY class.FirstLesson DESC;',
            $userId);
    }

    public function getUserHistory($userId)
    {
        return $this->database->query('
            SELECT semester.YearFrom, semester.YearTo, class.Name AS ClassName,
            mark.Shortcut, student.CertificateDate, student.IsActive
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

    public function getCertification($studentId, $classId)
    {
        return $this->database->query('
            SELECT semester.YearFrom, semester.YearTo, user.Name AS StudentName, student.Certificate, mark.Name AS MarkName,
            student.CertificateDate, class.Name AS ClassName, year.Number AS YearNumber, year.CodeName, student.HouseId
            FROM student
            INNER JOIN mark
            ON student.Certificate = mark.Id
            INNER JOIN user
            ON student.UserId = user.Id
            INNER JOIN class
            ON student.ClassId = class.Id
            INNER JOIN semester
            ON class.SemesterId = semester.Id
            INNER JOIN year
            ON class.YearId = year.Id
            WHERE student.UserId = ?
            AND student.ClassId = ?
            AND student.CertificateDate <= NOW();',
                $studentId, $classId);
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
            ?, @email, @password, @roleId)
            ON DUPLICATE KEY UPDATE
            Email = @email, Password = @password, RoleId = @roleId;',
            $values->username);
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

    public function updateLastLogin($userId)
    {
        return $this->database->query('
            UPDATE user
            SET LastLogin = NOW()
            WHERE Id = ?;',
            $userId);
    }

}