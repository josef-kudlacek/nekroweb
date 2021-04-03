<?php


namespace App\Model;

use Nette;

class ChangeLog
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getChangelog()
    {
        return $this->database->query('
            SELECT * FROM changelog');
    }

    public function getChangelogItemById($itemId)
    {
        return $this->database->table('changelog')->where('Id', $itemId);
    }

    public function insertChangelogItem($values)
    {
        return $this->database->table('changelog')
            ->insert($values);
    }

    public function updateChangelogItem($values)
    {
        return $this->database->table('changelog')
            ->where('Id', $values->Id)
            ->update($values);
    }

    public function deleteChangelogItem($itemId)
    {
        return $this->database->table('changelog')
            ->get($itemId)
            ->delete();
    }
}