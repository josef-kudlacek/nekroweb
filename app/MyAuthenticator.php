<?php


namespace App;

use App\Utils\Utils;
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

        $user = $this->findUserByUsername($username);

        if (!$this->passwords->verify($password, $user->Password)) {
            throw new Nette\Security\AuthenticationException('Invalid password.');
        }

        return $this->createIdentity($user);
    }

    public function changeUser($userId): Nette\Security\IIdentity
    {
        $user = $this->findUserById($userId);

        return $this->createIdentity($user);
    }

    public function isAllowed($role, $resource, $operation): bool
    {
        if ($role === 'Profesor') {
            return true;
        }
        if ($role === 'Student' && $resource === 'history') {
            return false;
        }

        return false;
    }

    public function forgotPassword($values)
    {
        try {
            $user = $this->findUser($values->username);
        } catch (Nette\Security\AuthenticationException $authenticationException) {
            throw $authenticationException;
        } catch (Nette\UnexpectedValueException $unexpectedValueException) {
            throw $unexpectedValueException;
        } catch (Nette\InvalidArgumentException $invalidArgumentException) {
            throw $invalidArgumentException;
        }


        if ($values->email != $user->Email) {
            throw new Nette\Security\AuthenticationException('Invalid email.');
        }

        $newPassword = Utils::generateString(12);
        $newHashPassword = $this->hash($newPassword);

        $this->database->query('
            UPDATE user
            SET Password = ?
            WHERE user.Name = ?;',
                $newHashPassword, $values->username);

        Utils::sendEmail($user->Email, 'Nekroweb: Žádost o nové heslo', $newPassword);
    }

    public function changePassword($values, $username)
    {
        try {
            $user = $this->findUser($username);
        } catch (Nette\Security\AuthenticationException $authenticationException) {
            throw $authenticationException;
        } catch (Nette\UnexpectedValueException $unexpectedValueException) {
            throw $unexpectedValueException;
        } catch (Nette\InvalidArgumentException $invalidArgumentException) {
            throw $invalidArgumentException;
        }

        if (!$this->passwords->verify($values->oldpassword, $user->Password)) {
            throw new Nette\Security\AuthenticationException('Invalid password.');
        }

        $this->setPassword($values->newpassword, $username);
    }

    protected function setPassword($newPassword, $username)
    {
        $this->database->query('
            UPDATE user
            SET Password = ?
            WHERE user.Name = ?;',
            $newPassword, $username);
    }

    public function hash($password)
    {
        return $this->passwords->hash($password);
    }

    public function findUserByUsername($username)
    {
        $params = array(
            ['user.Name' => $username],
        );

        try {
            return $this->findUserByParam($params);
        } catch (Nette\Security\AuthenticationException $authenticationException) {
            throw $authenticationException;
        } catch (Nette\UnexpectedValueException $unexpectedValueException) {
            throw $unexpectedValueException;
        } catch (Nette\InvalidArgumentException $invalidArgumentException) {
            throw $invalidArgumentException;
        }
    }

    public function findUserById($userId)
    {
        $params = array(
            ['user.Id' => $userId],
        );

        try {
            return $this->findUserByParam($params);
        } catch (Nette\Security\AuthenticationException $authenticationException) {
            throw $authenticationException;
        } catch (Nette\UnexpectedValueException $unexpectedValueException) {
            throw $unexpectedValueException;
        } catch (Nette\InvalidArgumentException $invalidArgumentException) {
            throw $invalidArgumentException;
        }
    }

    private function findUserByParam($params)
    {
        $user = $this->database->query('
            SELECT user.Id, user.Name, user.Email, user.Password, user.IsActive, 
            role.Name AS Role, student.ClassId as ClassId, class.Name AS Class,
            semester.YearFrom, semester.YearTo
            FROM user
            INNER JOIN role
            ON user.RoleId = role.Id
            LEFT JOIN student
			ON student.UserId = user.Id
			LEFT JOIN class
			ON student.ClassId = class.Id			
			LEFT JOIN semester
			ON semester.Id = class.SemesterId
			WHERE',
			$params,
			'ORDER BY class.FirstLesson DESC
			LIMIT 1;')
                ->fetch();

        if (!$user) {
            throw new Nette\Security\AuthenticationException('User not found.');
        }

        if (is_null($user->Email)) {
            throw new Nette\InvalidArgumentException('User has invalid credentials.');
        }

        if (!$user->IsActive) {
            throw new Nette\UnexpectedValueException('User is not active.');
        }

        return $user;
    }

    private function findUser($username)
    {
        return $this->findUserByUsername($username);
    }

    private function createIdentity($user) : Nette\Security\IIdentity
    {
        return new Nette\Security\Identity(
            $user->Id,
            $user->Role,
            ['name' => $user->Name,
                'email' => $user->Email,
                'className' => $user->Class,
                'classId' => $user->ClassId,
                'semesterFrom' => $user->YearFrom,
                'semesterTo' => $user->YearTo]
        );
    }
}