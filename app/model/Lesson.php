<?php


namespace App\Model;

use Nette;

class Lesson
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getLessonById($LessonId)
    {
        $params = array(
            ['lesson.Id' => $LessonId],
        );

        return $this->getLessonByParams($params);
    }

    public function getLessonsByYear($YearId)
    {
        $params = array(
            ['lesson.Year' => $YearId],
        );

        return $this->getLessonByParams($params);
    }

    public function getClassRemainingLessons($YearId, $ClassId)
    {
        return $this->database->query('
            SELECT lesson.Id, lesson.Number, lesson.Name
            FROM lesson
            WHERE lesson.Year = ?
            AND lesson.Id NOT IN
            (SELECT attendance.LessonId
            FROM attendance
            WHERE attendance.StudentClassId = ?)
            ORDER BY lesson.Number;',
            $YearId, $ClassId);
    }

    private function getLessonByParams($params)
    {
        return $this->database->query('
            SELECT Id, Number, Name
            FROM lesson            
            WHERE',
                $params,
            'ORDER BY lesson.Number;');
    }

}