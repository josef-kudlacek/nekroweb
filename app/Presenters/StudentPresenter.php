<?php


namespace App\Presenters;

use App\Model;

class StudentPresenter extends BasePresenter
{
    private $student;

    /** @var Model\Transaction
     * @inject
     */
    public $transaction;

    public function __construct(Model\Student $student)
    {
        $this->student = $student;
    }

    protected function startup()
    {
        parent::startup();
        if (!$this->getUser()->isInRole('Profesor')) {
            $this->flashMessage('Přístup do neoprávněné sekce. Proběhlo přesměrování na hlavní stránku.','danger');
            $this->redirect('Homepage:default');
        }
    }

    public function renderShow()
    {
        $semesterId = $this->getUser()->getIdentity()->semesterId;

        $this->template->students = $this->student->getActualStudents($semesterId);
    }

    public function actionDelete($studentId, $classId)
    {
        $this->transaction->startTransaction();
        $result = $this->student->deleteStudent($studentId, $classId);
        $this->transaction->endTransaction();

        if ($result !== 1) {
            $this->flashMessage('Studenta se nepodařilo odstranit ze semestru.', "danger");
        } else {
            $this->flashMessage('Student úspěšně odebrán ze třídy a semestru.', "success");
        }

        $this->redirect('Student:show');
    }

}