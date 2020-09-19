<?php


namespace App\Presenters;

use App\Model;
use App\MyAuthenticator;
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
}