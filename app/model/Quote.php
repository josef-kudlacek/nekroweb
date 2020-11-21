<?php


namespace App\Model;

use Nette;

class Quote
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getQuotes()
    {
        $params = array(
            ['1' => 1],
        );

        return $this->getQuotesByParams($params);
    }

    public function getQuoteById($quoteId)
    {
        $params = array(
            ['quote.Id' => $quoteId],
        );

        return $this->getQuotesByParams($params);
    }

    public function getQuoteByIdAndStudent($quoteId, $studentId)
    {
        $params = array(
            ['quote.Id' => $quoteId],
            ['quote.UserId' => $studentId],
        );

        return $this->getQuotesByParams($params);
    }

    public function getQuotesByStudent($studentId)
    {
        $params = array(
            ['quote.UserId' => $studentId],
        );

        return $this->getQuotesByParams($params);
    }

    public function insertQuote($values)
    {
        return $this->database->table('quote')
            ->insert($values);
    }

    public function updateQuote($values)
    {
        return $this->database->table('quote')
            ->where('Id = ?', $values['Id'])
            ->update($values);
    }

    public function deleteQuote($quoteId)
    {
        return $this->database->table('quote')
            ->where('Id = ?', $quoteId)
            ->delete();
    }

    private function getQuotesByParams($params)
    {
        return $this->database->query('
            SELECT quote.Id, user.Id AS UserId, class.Id AS ClassId, quote.Text, quote.Source, class.Name AS ClassName, user.Name AS UserName
            FROM quote
            INNER JOIN user
            ON quote.UserId = user.Id
            INNER JOIN class
            ON quote.ClassId = class.Id
            WHERE',
            $params,
            'ORDER BY quote.Id');
    }

}