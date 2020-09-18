<?php


namespace App\Model;

use Nette;

class Transaction
{
    use Nette\SmartObject;

    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function startTransaction()
    {
        $this->database->query("SET autocommit = 0;");
    }

    public function endTransaction()
    {
        $this->database->query("COMMIT;");
    }

}