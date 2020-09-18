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
    public $user;

    /** @var Model\StudyClass
     * @inject
     */
    public $studyClass;

    /** @var Model\Transaction
     * @inject
     */
    public $transaction;

    protected function createComponentRegisterForm(): Form
    {
        $form = new Form;

        $form->addText('username', 'Kouzelnické jméno')
            ->setRequired('Prosím vyplňte své kouzelnické jméno.')
            ->setMaxLength(64);

        $form->addText('email', 'Email:')
            ->setRequired('Prosím vyplňte svůj kontaktní email.')
            ->addRule(Form::EMAIL, 'Zadaný email nemá správný formát');

        $selectItems = Utils::prepareSelectBoxArray($this->studyClass->getAvailableClasses());

        $form->addSelect('class')
            ->setItems($selectItems)
            ->setRequired();

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím vyplňte své heslo.')
            ->setMaxLength(32);

        $form->addPassword('password2', 'Opakování hesla:')
            ->setRequired('Prosím vyplňte podruhé své heslo pro kontrolu.')
            ->setMaxLength(32)
            ->addRule(Form::EQUAL,'Vyplněná hesla se neshodují.', $form['password']);;

        $form->addSubmit('send', 'Zažádat');

        $form->addProtection();

        $form->onSuccess[] = [$this, 'registerFormSucceeded'];

        return $form;
    }

    public function registerFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $values->password = $this->authentication->hash($values->password);

            $this->transaction->startTransaction();
            $this->user->insertStudent($values);
            $this->transaction->endTransaction();

            $this->flashMessage('Žádost o přístup proběhla úspěšně. Počkejte na její schválení profesorem.' ,"success");
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

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];

        return $form;
    }

    public function signInFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $this->getUser()->login($values->username, $values->password);

            $this->flashMessage('Přihlášení proběhlo úspěšně.' ,"success");
            $this->redirect('Homepage:default');

        } catch (Nette\Security\AuthenticationException $authenticationException) {
            $this->flashMessage('Kouzelnické jméno nebo heslo není správně.' ,"danger");
        } catch (Nette\UnexpectedValueException $unexpectedValueException) {
            $this->flashMessage('Žádost o přístup nebyla doposud schválena. Pro urychlení zkuste kontaktovat pana profesora.' ,"danger");
        }
    }
}