<?php


namespace App\Presenters;

use App\Model;
use App\utils\Utils;
use Nette\Application\UI\Form;

class AttendancePresenter extends BasePresenter
{
    private $attendance;

    /** @var Model\StudyClass
     * @inject
     */
    public $studyClass;

    /** @var Model\Student
     * @inject
     */
    public $student;

    /** @var Model\AttendanceType
     * @inject
     */
    public $attendanceType;

    /** @var Model\ActivityType
     * @inject
     */
    public $activityTpe;

    public function __construct(Model\Attendance $attendance)
    {
        $this->attendance = $attendance;
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

        $this->template->classes = $this->studyClass->getClassesBySemester($semesterId);
        $this->template->classAttendance = $this->attendance->getAttendancesBySemesterId($semesterId);
    }

    public function renderDetail($ClassId, $LessonId)
    {
        $this->checkAccess();


    }

    public function renderCreate($ClassId)
    {
        $this->checkAccess();

        $this->template->students = $this->student->getStudentsByClassId($ClassId);
        $this->template->class = $this->studyClass->getClassById($ClassId)->fetch();
    }

    public function renderEdit($ClassId, $LessonId)
    {
        $this->checkAccess();
    }

    public function actionDelete($ClassId, $LessonId)
    {
        $this->checkAccess();
    }

    public function renderShow()
    {
        $userId = $this->user->getId();
        $classId = $this->user->getIdentity()->classId;


        $this->template->attendance = $this->attendance->getAttendanceByStudent($userId, $classId)->fetchAll();
    }

    public function renderClass()
    {
        $classId = $this->user->getIdentity()->classId;

        $this->template->attendance = $this->attendance->getAttendanceByClass($classId)->fetchAll();
    }

    protected function createComponentAttendanceForm(): Form
    {
        $form = new Form;

        $attendancetypes = $this->attendanceType->getAttendanceTypes()->fetchPairs('Id', 'Name');
        $form->addSelect('attendancetype')
            ->setItems($attendancetypes)
            ->setRequired();

        $form->addText('card')
            ->setMaxLength(4)
            ->addRule($form::INTEGER, 'Vložte platné číslo karty');

        $form->addCheckbox('isactive');

        $form->addSubmit('send', 'Upravit');

        $form->addProtection();

        $form->onSuccess[] = [$this, 'attendanceFormSucceeded'];

        return $form;
    }

    public function attendanceFormSucceeded(Form $form, \stdClass $values): void
    {
        $studentId = $this->getParameter('studentId');
        $values = Utils::convertEmptyToNull($form->getValues());

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

    private function checkAccess()
    {
        if (!$this->getUser()->isInRole('Profesor')) {
            $this->flashMessage('Přístup do neoprávněné sekce. Proběhlo přesměrování na hlavní stránku.','danger');
            $this->redirect('Homepage:default');
        }
    }
}