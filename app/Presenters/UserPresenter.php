<?php


namespace App\Presenters;

use App\Model;
use App\MyAuthenticator;
use App\Utils\Utils;
use Nette;
use Nette\Application\UI\Form;

class UserPresenter extends BasePresenter
{
    /** @var MyAuthenticator
     * @inject
     */
    public $authentication;

    /** @var Model\Activity
     * @inject
     */
    public $activity;

    /** @var Model\StudyClass
     * @inject
     */
    public $studyClass;

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
            $this->flashMessage('Přístup do této sekce je pouze pro přihlášené. Přihlaste se prosím.','danger');
            $this->redirect('Sign:in');
        }
    }

    public function actionOverview()
    {
        if (!$this->getUser()->isInRole('Student')) {
            $this->flashMessage('Přístup do neoprávněné sekce. Proběhlo přesměrování na hlavní stránku.','danger');
            $this->redirect('Homepage:default');
        }

        $studentId = $this->getUser()->getId();
        $classId = $this->getUser()->getIdentity()->classId;

        $this->template->points = $this->dbUser->getStudentSum($studentId, $classId)->fetch();
        $this->template->overview = $this->studyClass->getOverviewByStudent($studentId, $classId)->fetch();
    }

    public function actionDeleteUser()
    {
        $this->transaction->startTransaction();
        $this->dbUser->deleteUser($this->user->getId());
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

    public function actionChange()
    {
        if (!$this->getUser()->isInRole('Profesor')) {
            $this->flashMessage('Přístup do neoprávněné sekce. Proběhlo přesměrování na hlavní stránku.','danger');
            $this->redirect('Homepage:default');
        }
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

        $form->onError[] = array($this, 'errorForm');
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

    protected function createComponentChangeUserForm(): Form
    {
        $form = new Form;

        $users = $this->dbUser->getUsers()->fetchPairs('Id', 'Name');

        $form->addSelect('user')
            ->setItems($users)
            ->setRequired();

        $form->addSubmit('send', 'Změnit uživatele');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'changeUserFormSucceeded'];

        return $form;
    }

    public function changeUserFormSucceeded(Form $form, \stdClass $values): void
    {
        if (!$this->getUser()->isInRole('Profesor')) {
            $this->flashMessage('Přístup do neoprávněné sekce. Proběhlo přesměrování na hlavní stránku.','danger');
            $this->redirect('Homepage:default');
        }

        try {
            $currentUser = $this->user->getId();

            $newUser =$this->authentication->changeUser($values->user);
            $this->user->login($newUser);
            $this->user->getIdentity()->oldIdentity = $currentUser;

            $semesterTo = (!is_null($this->user->getIdentity()->semesterFrom) ? '/' . $this->user->getIdentity()->semesterTo : '');
            $this->flashMessage('Uživatel změněn. Vítej ' . $this->user->getIdentity()->name .' ve třídě '. $this->user->getIdentity()->className .
                ', školní rok: '. $this->user->getIdentity()->semesterFrom . $semesterTo . '!' ,"success");
            $this->redirect('Homepage:default');

        } catch (Nette\Security\AuthenticationException $authenticationException) {
            $this->flashMessage('Nepodařilo se změnit uživatele.' ,"danger");
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

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'changeClassFormSucceeded'];

        return $form;
    }

    public function changeClassFormSucceeded(Form $form, \stdClass $values): void
    {
        $studentId = $this->user->getId();
        $class = $this->studyClass->getStudentClassById($values->class, $studentId)->fetch();

        try {
            $this->user->getIdentity()->classId = $class->ClassId;
            $this->user->getIdentity()->className = $class->Name;
            $this->user->getIdentity()->semesterFrom = $class->YearFrom;
            $this->user->getIdentity()->semesterTo = $class->YearTo;

            $semesterTo = (!is_null($class->YearTo) ? '/' .$class->YearTo : '');
            $this->flashMessage('Třída úspěšně změněna. Vítej ve třídě '. $class->Name .
                ', školní rok: '. $class->YearFrom . $semesterTo . '!' ,"success");
            $this->redirect('User:overview');

        } catch (Nette\Security\AuthenticationException $authenticationException) {
            $this->flashMessage('Původní heslo nesouhlasí. Heslo nemohlo být změněno.' ,"danger");
        }
    }
}