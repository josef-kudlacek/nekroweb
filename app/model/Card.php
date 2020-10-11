<?php


namespace App\Model;

use Nette;

class Card
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getCardsBySemester($semesterId)
    {
        return $this->database->query('
            SELECT card.Id, card.CardNumber, card.Reason, card.Date,
            student.HouseId, user.Id AS StudentId, user.Name AS StudentName,
            class.Id AS ClassId, class.Name AS ClassName
            FROM card
            INNER JOIN student
            ON card.StudentUserId = student.UserId
            AND card.StudentClassId = student.ClassId
            INNER JOIN user
            ON student.UserId = user.Id
            INNER JOIN class
            ON class.Id = student.ClassId
            WHERE class.SemesterId = ?
            ORDER BY class.Name, user.Name, card.Date;',
                $semesterId);
    }

    public function getCardById($cardId)
    {
        return $this->database->query('
            SELECT *
            FROM card
            WHERE card.Id = ?;',
                $cardId);
    }

    public function getCardsByClass($ClassId)
    {
        return $this->database->query('
            SELECT card.Id, card.CardNumber, card.Reason, card.Date,
            student.HouseId, user.Name AS StudentName
            FROM card
            INNER JOIN student
            ON card.StudentUserId = student.UserId
            AND card.StudentClassId = student.ClassId
            INNER JOIN user
            ON student.UserId = user.Id
            WHERE card.StudentClassId = ?;',
                $ClassId);
    }

    public function getCardsByStudent($StudentId, $ClassId)
    {
        return $this->database->query('
            SELECT card.Id, card.CardNumber, card.Reason, card.Date,
            student.HouseId
            FROM card
            INNER JOIN student
            ON card.StudentUserId = student.UserId
            AND card.StudentClassId = student.ClassId
            WHERE card.StudentUserId = ?
            AND card.StudentClassId = ?;',
                $StudentId, $ClassId);
    }

    public function insertCard($values)
    {
        return $this->database->table('card')
            ->insert($values);
    }

    public function updateCard($values)
    {
        return $this->database->table('card')
            ->where('Id', $values->Id)
            ->update($values);
    }

    public function deleteCard($CardId)
    {
        return $this->database->table('card')
            ->get($CardId)
            ->delete();
    }

}