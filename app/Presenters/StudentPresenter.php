<?php


namespace App\Presenters;

use App\Model;
use App\Utils\Utils;
use Nette\Application\UI\Form;

class StudentPresenter extends BasePresenter
{
    private $student;

    /** @var Model\Transaction
     * @inject
     */
    public $transaction;

    /** @var Model\User
     * @inject
     */
    public $dbUser;

    /** @var Model\StudyClass
     * @inject
     */
    public $studyClass;

    /** @var Model\House
     * @inject
     */
    public $house;

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

        $this->template->semesterId = hash('ripemd160', $semesterId);
        $this->template->students = $this->student->getStudentsBySemesterId($semesterId);
    }

    public function actionActive($studentId, $studentName)
    {
        $this->dbUser->activeUser($studentId);
        $this->flashMessage('Studentovi jménem '. $studentName .' byl povolen přístup na nekroweb.','success');
        $this->redirect('Student:show');
    }

    public function actionDelete($studentId, $classId)
    {
        $this->transaction->startTransaction();
        $this->student->setActive($studentId, $classId, 0);
        $this->transaction->endTransaction();

        $this->flashMessage('Student vyřazen ze třídy.', "success");
        $this->redirect('Student:show');
    }

    public function actionAdd($studentId, $classId)
    {
        $this->transaction->startTransaction();
        $this->student->setActive($studentId, $classId, 1);
        $this->transaction->endTransaction();

        $this->flashMessage('Student přidán do třídy.', "success");
        $this->redirect('Student:show');
    }

    public function actionEdit($studentId, $classId)
    {
        $student = $this->student->getStudent($studentId, $classId)->fetch();
        if (!$student) {
            $this->flashMessage('Student nenalezen.', "danger");;
            $this->redirect('Student:show');
        }

        $this->template->student = $student;
        $this['studentForm']->setDefaults([
                'username' => $student->UserName,
                'email' => $student->Email,
                'class' => $student->ClassId,
                'house' => $student->HouseId,
                'isactive' => $student->IsActive
        ]);
    }

    protected function createComponentStudentForm(): Form
    {
        $form = new Form;

        $form->addText('username')
            ->setRequired('Prosím vyplňte kouzelnické jméno.')
            ->setMaxLength(64);

        $form->addText('email');

        $semesterId = $this->user->getIdentity()->semesterId;
        $selectClasses = Utils::prepareSelectBoxArray($this->studyClass->getClassesBySemester($semesterId));

        $form->addSelect('class')
            ->setItems($selectClasses)
            ->setRequired();

        $selectHouses = $this->house->getHouses()->fetchPairs('Id', 'Name');

        $form->addSelect('house')
            ->setItems($selectHouses)
            ->setRequired();

        $form->addCheckbox('isactive');

        $form->addSubmit('send', 'Upravit');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'studentFormSucceeded'];

        return $form;
    }

    public function studentFormSucceeded(Form $form, \stdClass $values): void
    {
        $studentId = $this->getParameter('studentId');
        $values = Utils::convertEmptyToNull($form->getValues());
        $values->isactive = (int) $values->isactive;

        $this->transaction->startTransaction();

        if ($studentId) {
            $result = $this->student->updateStudent($studentId, $values);

            if ($result < 0) {
                $this->flashMessage('Student má již záznamy vázané k třídě a nelze mu ji změnit.','info');
            }

        } else {
            $this->student->insertStudent($values);
        }


        $this->transaction->endTransaction();
        $this->redirect('Student:show');
    }

}