<?php


namespace App\Model;

use Nette;

class Curriculum
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getLessons()
    {
        $params = array(
            ['year.IsActive' => 1],
        );

        return $this->getLessonsByParams($params);
    }

    public function getLessonsByUserAndSemester($userId, $semesterFrom, $semesterTo)
    {
        $semesterTo = isset($semesterTo) ? $semesterTo : $semesterFrom;

        $params = array(
            [$this->database::literal('lesson.Year IN
                (SELECT year.Id
                FROM class
                INNER JOIN necromancy.year year
                	ON class.YearId = year.Id
                INNER JOIN student
                	ON class.Id = student.ClassId
                INNER JOIN semester
                	ON class.SemesterId = semester.Id
                WHERE student.UserId = ?
                AND semester.YearFrom <= ?
                AND (semester.YearTo <= ? OR semester.YearTo IS NULL)
                )', $userId, $semesterFrom, $semesterTo)],
        );

        return $this->getLessonsByParams($params);
    }

    public function getLessonYears()
    {
        return $this->database->query('
            SELECT *
            FROM necromancy.year year;');
    }

    private function getLessonsByParams($params)
    {
        return $this->database->query('
            SELECT lesson.Id, year.Number AS YearNumber, year.CodeName, lesson.Number AS LessonNumber, lesson.Name 
            FROM necromancy.lesson lesson
            INNER JOIN necromancy.year year
            ON lesson.Year = year.Id
            WHERE',
            $params,
            'GROUP BY year.Number, lesson.Number
            ORDER BY year.Number, lesson.Number;');
    }
}