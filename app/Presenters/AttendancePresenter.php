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

    /** @var Model\User
     * @inject
     */
    public $dbUser;

    /** @var Model\AttendanceType
     * @inject
     */
    public $attendanceType;

    /** @var Model\ActivityType
     * @inject
     */
    public $activityTpe;

    /** @var Model\Lesson
     * @inject
     */
    public $lesson;

    /** @var Model\Transaction
     * @inject
     */
    public $transaction;

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

    public function actionAdmin()
    {
        $this->checkAccess();

        $semesterId = $this->getUser()->getIdentity()->semesterId;

        $this->template->classes = $this->studyClass->getClassesBySemester($semesterId);
        $this->template->classAttendance = $this->attendance->getAttendancesBySemesterId($semesterId);
    }

    public function actionDetail($ClassId, $LessonId)
    {
        $this->checkAccess();

        $this->template->lesson = $this->lesson->getLessonById($LessonId)->fetch();
        $this->template->class = $this->studyClass->getClassById($ClassId)->fetch();
        $this->template->attendance = $this->attendance->getClassAttendanceSummary($ClassId, $LessonId)->fetchAll();
    }

    public function handleExcuse($AttendanceId, $StudentId)
    {
        $this->checkAccess();

        $this->transaction->startTransaction();
        $this->attendance->excuseStudent($AttendanceId);
        $this->transaction->endTransaction();

        $student = $this->dbUser->GetUserById($StudentId)->fetch();
        $this->flashMessage('Studentovi jménem '. $student->Name .' byla omluvena hodina.','success');

        $classId = $this->getParameter("ClassId");
        $lessonId = $this->getParameter("LessonId");
        $this->redirect('Attendance:detail', array($classId, $lessonId));
    }

    public function actionCreate($ClassId)
    {
        $this->checkAccess();

        $this->template->attendancetypes = $this->attendanceType->getAttendanceTypes()->fetchAll();
        $this->template->students = $this->student->getStudentsByClassId($ClassId);
        $this->template->class = $this->studyClass->getClassById($ClassId)->fetch();

        $YearId = $this->template->class->YearId;
        $this->template->lessons =$this->lesson->getLessonsByYear($YearId)->fetchAll();
    }

    public function renderEdit($ClassId, $LessonId)
    {
        $this->checkAccess();

        $this->template->class = $this->studyClass->getClassById($ClassId)->fetch();
        $this->template->attendancetypes = $this->attendanceType->getAttendanceTypes()->fetchAll();
        $this->template->classAttendance = $this->attendance->GetAttendancesByClassAndLesson($ClassId, $LessonId)->fetchAll();

        $YearId = $this->template->class->YearId;
        $this->template->lessons =$this->lesson->getLessonsByYear($YearId)->fetchAll();
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

        $form->addSelect('LessonId');

        $form->addText('AttendanceDate')
            ->setRequired();

        $form->addInteger('StudentClassId');

        $form->addInteger('StudentUserId');

        $form->addCheckbox('AttendanceTypeId');

        $form->addInteger('AttendanceCard');

        $form->addSubmit('send', 'Zapsat');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'attendanceFormSucceeded'];

        return $form;
    }

    public function attendanceFormSucceeded(Form $form, \stdClass $values): void
    {
        $this->checkAccess();

        $values = $form->getHttpData($form::DATA_TEXT);
        $values = $this->prepareAttendanceData($values);

        $this->transaction->startTransaction();
        if ($this->getParameter('LessonId'))
        {
            $values = Utils::convertEmptyToNull($values);
            $this->attendance->updateAttendances($values, $this->getParameter('LessonId'));

            $this->transaction->endTransaction();
            $this->redirect('Activity:edit', array($values[0]['StudentClassId'], $values[0]['LessonId']));
        } else {
            $values = Utils::convertEmptyToNull($values);
            $this->attendance->insertAttendances($values);

            $this->transaction->endTransaction();
            $this->redirect('Activity:create', array($values[0]['StudentClassId'], $values[0]['LessonId']));
        }
    }

    private function checkAccess()
    {
        if (!$this->getUser()->isInRole('Profesor')) {
            $this->flashMessage('Přístup do neoprávněné sekce. Proběhlo přesměrování na hlavní stránku.','danger');
            $this->redirect('Homepage:default');
        }
    }

    private function prepareAttendanceData($values)
    {
        $studentIds = array_keys($values['StudentUserId']);
        $studentsData = array();


        foreach ($studentIds as $student) {
            $studentItem = array(
                "StudentUserId" => $values['StudentUserId'][$student],
                "StudentClassId" => $values['StudentClassId'][$student],
                "LessonId" => $values['LessonId'],
                "AttendanceDate" => $values['AttendanceDate'],
                "AttendanceTypeId" => $values['AttendanceTypeId'][$student],
                "AttendanceCard" => $values['AttendanceCard'][$student],
            );

            array_push($studentsData, $studentItem);
        }

        return $studentsData;
    }
}