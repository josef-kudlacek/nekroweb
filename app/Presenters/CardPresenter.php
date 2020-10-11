<?php


namespace App\Presenters;

use App\Model;
use App\Utils\Utils;
use Nette\Application\UI\Form;

class CardPresenter extends BasePresenter
{
    private $card;

    /** @var Model\StudyClass
     * @inject
     */
    public $studyClass;

    /** @var Model\Student
     * @inject
     */
    public $student;

    /** @var Model\Transaction
     * @inject
     */
    public $transaction;

    public function __construct(Model\Card $card)
    {
        $this->card = $card;
    }

    protected function startup()
    {
        parent::startup();
        if (!$this->getUser()->loggedIn) {
            $this->flashMessage('Přístup do této sekce je pouze pro přihlášené. Přihlaste se prosím.','danger');
            $this->redirect('Sign:in');
        }
    }

    public function renderAdmin()
    {
        $this->checkAccess();
        $semesterId = $this->getUser()->getIdentity()->semesterId;

        $this->template->cards = $this->card->getCardsBySemester($semesterId);
    }

    public function renderShow()
    {
        $studentId = $this->user->getId();
        $classId = $this->user->getIdentity()->classId;

        $this->template->cards = $this->card->getCardsByStudent($studentId, $classId);
    }

    public function renderClass()
    {
        $classId = $this->user->getIdentity()->classId;

        $this->template->cards = $this->card->getCardsByClass($classId);
    }

    public function renderCreate()
    {
        $this->checkAccess();
    }

    public function renderEdit($cardId)
    {
        $this->checkAccess();

        $card = $this->card->getCardById($cardId)->fetch();

        $card->Date = $card->Date->format('Y-m-d\TH:i:s');
        $this['cardForm']->setDefaults($card);
    }

    public function actionDelete($cardId)
    {
        $this->checkAccess();

        $this->transaction->startTransaction();
        $this->card->deleteCard($cardId);
        $this->transaction->endTransaction();

        $this->flashMessage('Karta úspěšně odebrána.','success');
        $this->redirect('Card:admin');
    }

    protected function createComponentCardForm(): Form
    {
        $SemesterId = $this->getUser()->getIdentity()->semesterId;

        $students = $this->student->getActualStudents($SemesterId)->fetchPairs('UserId', 'UserName');
        $classess = $this->studyClass->getClassesBySemester($SemesterId)->fetchPairs('ClassId', 'Name');

        $form = new Form;

        $form->addText('Id');

        $form->addSelect('StudentUserId')
            ->setItems($students)
            ->setRequired();

        $form->addSelect('StudentClassId')
            ->setItems($classess)
            ->setRequired();

        $form->addText('CardNumber')
            ->setRequired()
            ->setMaxLength(4);

        $form->addTextArea('Reason')
            ->setRequired();

        $form->addText('Date');

        $form->addSubmit('send');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'cardFormSucceeded'];

        return $form;
    }

    public function cardFormSucceeded(Form $form, \stdClass $values): void
    {

        $values = Utils::convertEmptyToNull($form->values);

        $this->transaction->startTransaction();

        if ($values->Id)
        {
            $this->card->updateCard($values);

            $this->flashMessage('Udělení karty úspěšně upraveno.','success');
        } else {
            $values['Date'] = date('Y-m-d H:i:s');
            $this->card->insertCard($values);

            $this->flashMessage('Karta úspěšně udělena.','success');
        }

        $this->transaction->endTransaction();

        $this->redirect('Card:admin');
    }


    private function checkAccess()
    {
        if (!$this->getUser()->isInRole('Profesor')) {
            $this->flashMessage('Přístup do neoprávněné sekce. Proběhlo přesměrování na hlavní stránku.','danger');
            $this->redirect('Homepage:default');
        }
    }
}