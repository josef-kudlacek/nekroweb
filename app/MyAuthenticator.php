<?php


namespace App;

use Nette;


class MyAuthenticator implements Nette\Security\IAuthenticator
{
    private $database;
    private $passwords;

    public function __construct(Nette\Database\Context $database, Nette\Security\Passwords $passwords)
    {
        $this->database = $database;
        $this->passwords = $passwords;
    }

    public function authenticate(array $credentials): Nette\Security\IIdentity
    {
        [$username, $password] = $credentials;

        $row = $this->database->query('
            SELECT user.Id, user.Name, user.Email, user.Password, user.IsActive, role.Name AS Role
            FROM user
            INNER JOIN role
            ON user.RoleId = role.Id;')
            ->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException('User not found.');
        }

        if (!$this->passwords->verify($password, $row->Password)) {
            throw new Nette\Security\AuthenticationException('Invalid password.');
        }

        if (!$row->IsActive) {
            throw new Nette\UnexpectedValueException('User is not active.');
        }

        return new Nette\Security\Identity(
            $row->Id,
            $row->Role,
            ['name' => $row->Name, 'email' => $row->Email]
        );
    }

    public function hash($password)
    {
        return $this->passwords->hash($password);
    }
}