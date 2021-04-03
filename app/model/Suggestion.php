<?php


namespace App\Model;

use Nette;

class Suggestion
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getSuggestionParents()
    {
        return $this->database->query('
            SELECT suggestion.Id
            FROM suggestion
            WHERE suggestion.ParentId IS NULL;');
    }

    public function getSuggestions()
    {
        $params = array(
            ['1' => 1]
        );

        return $this->getSuggestionsByParam($params);
    }

    public function getSuggestionsByParent($suggestionParentId)
    {
        $params = array(
            ['suggestion.ParentId' => $suggestionParentId]
        );

        return $this->getSuggestionsByParam($params);
    }

    public function getSuggestionById($suggestionId)
    {
        $params = array(
            ['suggestion.Id' => $suggestionId]
        );

        return $this->getSuggestionsByParam($params);
    }

    public function getUserSuggestion($suggestionId, $userId)
    {
        $params = array(
            ['suggestion.Id' => $suggestionId],
            ['suggestion.UserId' => $userId]
        );

        return $this->getSuggestionsByParam($params);
    }

    public function insertSuggestion($values)
    {
        return $this->database->table('suggestion')->insert($values);
    }

    public function updateSuggestion($values)
    {
        return $this->database->query("
            UPDATE suggestion
            SET Subject = ?,
            Text = ?,
            UDatetime = ?    
            WHERE suggestion.Id = ?;",
            $values->Subject, $values->Text, $values->Datetime, $values->Id);
    }

    private function getSuggestionsByParam($params)
    {
        return $this->database->query('
            SELECT suggestion.Id, suggestion.ParentId, suggestion.Subject, suggestion.Datetime,
            suggestion.Text, suggestion.UDatetime,
            user.Name AS UserName, user.Id AS UserId, class.Name AS ClassName, student.HouseId,
            semester.YearFrom, semester.YearTo
            FROM suggestion
            INNER JOIN user
            ON user.Id = suggestion.UserId
            LEFT JOIN student
            ON user.Id = student.UserId            
            LEFT JOIN class
            ON class.Id = student.ClassId
            LEFT JOIN semester
            ON class.SemesterId = semester.Id
            WHERE',
            $params,
            'GROUP BY suggestion.id, user.Id
            ORDER BY suggestion.Datetime ASC;');
    }
}