<?php


namespace App\Presenters;

use App\Model;
use App\MyAuthenticator;
use App\utils\Utils;
use Nette;
use Nette\Application\UI\Form;

class UserPresenter extends BasePresenter
{
    /** @var MyAuthenticator
     * @inject
     */
    public $authentication;

    /** @var Model\Transaction
     * @inject
     */
    public $transaction;


    /** @var Model\User
     * @inject
     */
    public $dbUser;

    protected function startup()
    {
        parent::startup();
        if (!$this->getUser()->loggedIn) {
            $this->flashMessage('Přístup do této sekce je pouze pro přihlášené!','danger');
            $this->redirect('Homepage:default');
        }
    }

    public function actionDeleteUser()
    {
        $this->transaction->startTransaction();
        $this->dbUser->deleteUser($this->user->getIdentity()->name);
        $this->transaction->endTransaction();

        $this->user->logout(true);

        $this->flashMessage('Odhlášení proběhlo úspěšně. Citlivé údaje byly odstraněny z databáze.','info');
        $this->redirect('Homepage:default');
    }

    public function renderHistory()
    {
        $userId = $this->user->getId();
        $this->template->history = $this->dbUser->getUserHistory($userId);
    }

    protected function createComponentChangePassForm(): Form
    {
        $form = new Form;

        $form->addPassword('oldpassword')
            ->setRequired('Prosím vyplňte své původní heslo.')
            ->setMaxLength(32);

        $form->addPassword('newpassword')
            ->setRequired('Prosím vyplňte své nové heslo.')
            ->setMaxLength(32);

        $form->addPassword('newpassword2')
            ->setRequired('Prosím vyplňte podruhé své nové heslo pro kontrolu.')
            ->setMaxLength(32)
            ->addRule(Form::EQUAL,'Vyplněné nové heslo není stejné.', $form['newpassword']);

        $form->addSubmit('send', 'Změnit heslo');

        $form->addProtection();

        $form->onSuccess[] = [$this, 'changePassFormSucceeded'];

        return $form;
    }

    public function changePassFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $values->newpassword = $this->authentication->hash($values->newpassword);

            $this->transaction->startTransaction();
            $this->authentication->changePassword($values, $this->user->getIdentity()->name);
            $this->transaction->endTransaction();

            $this->flashMessage('Heslo bylo úspěšně změněno.' ,"success");

        } catch (Nette\Security\AuthenticationException $authenticationException) {
            $this->flashMessage('Původní heslo nesouhlasí. Heslo nemohlo být změněno.' ,"danger");
        }
    }

    protected function createComponentChangeClassForm(): Form
    {
        $form = new Form;

        $this->template->classes = $this->dbUser->getUserClasses($this->user->getId());

        $selectItems = Utils::prepareSelectBoxArray($this->template->classes);

        $form->addSelect('class')
            ->setItems($selectItems)
            ->setRequired();

        $form->addSubmit('send', 'Přepnout třídu');

        $form->addProtection();

        $form->onSuccess[] = [$this, 'changeClassFormSucceeded'];

        return $form;
    }

    public function changeClassFormSucceeded(Form $form, \stdClass $values): void
    {
        $classes = $this->template->classes->fetchAll();
        $classId = array_search($values->class, array_column($classes, 'ClassId'));
        $class = $classes[$classId];

        try {
            $this->user->getIdentity()->classId = $class->ClassId;
            $this->user->getIdentity()->className = $class->Name;
            $this->user->getIdentity()->semesterFrom = $class->YearFrom;
            $this->user->getIdentity()->semesterTo = $class->YearTo;

            $this->flashMessage('Třída úspěšně změněna. Vítej ve třídě '. $class->Name .'!' ,"success");
            $this->redirect('User:data');

        } catch (Nette\Security\AuthenticationException $authenticationException) {
            $this->flashMessage('Původní heslo nesouhlasí. Heslo nemohlo být změněno.' ,"danger");
        }
    }
}