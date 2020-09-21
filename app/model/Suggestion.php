<?php


namespace App\model;

use Nette;

class Suggestion
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function GetSuggestions()
    {
        return $this->database->query('
            SELECT suggestion.Id, user.Name, suggestion.Datetime, suggestion.Text
            FROM suggestion
            INNER JOIN user
            ON user.Id = suggestion.UserId;');
    }

    public function GetSuggestionComments()
    {
        return $this->database->query('
            SELECT com.SuggestionId, user.Name, com.Datetime, com.Text
            FROM suggestioncomment com
            INNER JOIN user
            ON user.Id = com.UserId;');
    }
}