<?php


namespace App\Presenters;

use App\Model;
use App\MyAuthenticator;
use Nette;
use Nette\Application\UI\Form;
use App\Utils\Utils;

class SignPresenter extends BasePresenter
{
    /** @var MyAuthenticator
     * @inject
     */
    public $authentication;

    /** @var Model\User
     * @inject
     */
    public $dbUser;

    /** @var Model\StudyClass
     * @inject
     */
    public $studyClass;

    /** @var Model\Transaction
     * @inject
     */
    public $transaction;

    /** @var Model\Semester
     * @inject
     */
    public $semester;

    /** @var Model\House
     * @inject
     */
    public $house;

    public function actionLogout()
    {
        if ($this->getUser()->getIdentity()->oldIdentity)
        {
            $oldId = $this->getUser()->getIdentity()->oldIdentity;
            $newUser =$this->authentication->changeUser($oldId);
            $this->user->login($newUser);
            Utils::setActualSemester($this->getUser()->getIdentity(), $this->semester->GetActualSemester());

        } else {
            $this->getUser()->logout(true);
        }

        $this->flashMessage('Odhlášení proběhlo úspěšně.','success');
        $this->redirect('Homepage:default');
    }

    protected function createComponentRegisterForm(): Form
    {
        $form = new Form;

        $form->addText('username', 'Kouzelnické jméno')
            ->setRequired('Prosím vyplňte své kouzelnické jméno.')
            ->setMaxLength(64);

        $selectHouses = $this->house->getHouses()->fetchPairs('Id', 'Name');

        $form->addSelect('house')
            ->setItems($selectHouses);

        $form->addText('email', 'Email:')
            ->setRequired('Prosím vyplňte svůj kontaktní email.')
            ->addRule(Form::EMAIL, 'Zadaný email nemá správný formát');

        $selectItems = Utils::prepareSelectBoxArray($this->studyClass->getAvailableClasses());

        $form->addSelect('class')
            ->setItems($selectItems)
            ->setRequired();

        $form->addPassword('password')
            ->setRequired('Prosím vyplňte své heslo.')
            ->setMaxLength(32);

        $form->addPassword('password2')
            ->setRequired('Prosím vyplňte podruhé své heslo pro kontrolu.')
            ->setMaxLength(32)
            ->addRule(Form::EQUAL,'Vyplněná hesla se neshodují.', $form['password']);

        $form->addCheckbox('agreement')
            ->setRequired('Musíte souhlasit s podmínkami používání!');

        $form->addSubmit('send', 'Zažádat');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'registerFormSucceeded'];

        return $form;
    }

    public function registerFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $values->password = $this->authentication->hash($values->password);

            $this->transaction->startTransaction();
            $this->dbUser->insertStudent($values);
            $this->transaction->endTransaction();

            $this->flashMessage('Žádost o přístup proběhla úspěšně. Pro její schválení napište sovu profesorovi a sdělte mu pro potvrzení použitý mail.' ,"success");
            $this->redirect('Homepage:default');

        } catch (Nette\Security\AuthenticationException $authenticationException) {
            $this->flashMessage('Žádost o přístup se nezdařila. Kontaktujte pana profesora.' ,"danger");
        } catch (Nette\Database\UniqueConstraintViolationException $uniqueConstraintViolationExceptione) {
            $this->flashMessage('Žádost o přístup byla zamítnuta. Kouzelnické jméno nebo email jsou již používané.' ,"danger");
        }
    }

    protected function createComponentSignInForm(): Form
    {
        $form = new Form;

        $form->addText('username', 'Kouzelnické jméno')
            ->setRequired('Prosím vyplňte své kouzelnické jméno.')
            ->setMaxLength(64);

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím vyplňte své heslo.')
            ->setMaxLength(32);

        $form->addSubmit('send', 'Přihlásit');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'signInFormSucceeded'];

        return $form;
    }

    public function signInFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $this->getUser()->login($values->username, $values->password);
            if ($this->getUser()->isInRole('Profesor')) {
                Utils::setActualSemester($this->getUser()->getIdentity(), $this->semester->GetActualSemester());
            }

            $this->flashMessage('Přihlášení proběhlo úspěšně.' ,"success");
            $this->redirect('Homepage:default');

        } catch (Nette\Security\AuthenticationException $authenticationException) {
            $this->flashMessage('Kouzelnické jméno nebo heslo není správně.' ,"danger");
        } catch (Nette\UnexpectedValueException $unexpectedValueException) {
            $this->flashMessage('Žádost o přístup nebyla doposud schválena. Pro urychlení zkuste kontaktovat pana profesora.' ,"danger");
        } catch (Nette\InvalidArgumentException $invalidArgumentException) {
            $this->flashMessage('Kouzelnické jméno nenalezeno.' ,"danger");
        }
    }

    protected function createComponentForgotForm(): Form
    {
        $form = new Form;

        $form->addText('username', 'Kouzelnické jméno')
            ->setRequired('Prosím vyplňte své kouzelnické jméno.')
            ->setMaxLength(64);

        $form->addText('email', 'Email:')
            ->setRequired('Prosím vyplňte svůj kontaktní email.')
            ->addRule(Form::EMAIL, 'Zadaný email nemá správný formát');

        $form->addSubmit('send', 'Odeslat nové heslo');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'forgotFormSucceeded'];

        return $form;
    }

    public function forgotFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $this->transaction->startTransaction();
            $this->authentication->forgotPassword($values);
            $this->transaction->endTransaction();

            $this->flashMessage('Na email ' . $values->email . ' bylo zasláno nové heslo. Zkontrolujte případně spam.' ,"success");
            $this->redirect('Homepage:default');

        } catch (Nette\Security\AuthenticationException $authenticationException) {
            $this->flashMessage('Kouzelnické jméno nebo email nenalezeny.' ,"danger");
        } catch (Nette\UnexpectedValueException $unexpectedValueException) {
            $this->flashMessage('Doposud nebyla schválena profesorem žádost o přístup a proto není možné vygenerovat nové heslo. Obraťte se na profesora.' ,"danger");
        }
    }
}