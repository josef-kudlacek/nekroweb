<?php


namespace App\Presenters;

use App\Model;
use App\Utils\Utils;
use Nette\Application\UI\Form;

class OverviewPresenter extends BasePresenter
{
    /** @var Model\Attendance
     * @inject
     */
    public $attendance;

    /** @var Model\StudyClass
     * @inject
     */
    public $studyClass;

    /** @var Model\Student
     * @inject
     */
    public $student;

    /** @var Model\StudentAssessment
     * @inject
     */
    public $studentAssessment;

    /** @var Model\Mark
     * @inject
     */
    public $mark;

    /** @var Model\Transaction
     * @inject
     */
    public $transaction;

    protected function startup()
    {
        parent::startup();
        if (!$this->getUser()->isInRole('Profesor')) {
            $this->flashMessage('Přístup do neoprávněné sekce. Proběhlo přesměrování na hlavní stránku.','danger');
            $this->redirect('Homepage:default');
        }
    }

    public function renderAttendance()
    {
        $SemesterId = $this->getUser()->getIdentity()->semesterId;
        $this->template->attendance = $this->attendance->getAttendanceBySemester($SemesterId);
    }

    public function renderAssessment()
    {
        $SemesterId = $this->getUser()->getIdentity()->semesterId;
        $this->template->assessments = $this->studentAssessment->getStudentAssessmentsBySemester($SemesterId);
    }

    public function renderPoints()
    {
        $SemesterId = $this->getUser()->getIdentity()->semesterId;
        $this->template->students = $this->studyClass->getPointsSumBySemesterId($SemesterId);
    }

    public function renderShow()
    {
        $SemesterId = $this->getUser()->getIdentity()->semesterId;
        $this->template->students = $this->studyClass->getOverview($SemesterId);
    }

    public function renderCertificate($StudentId, $ClassId)
    {
        $certification = $this->student->getCertificationInfo($StudentId, $ClassId)->fetch();
        $certification->CertificateDate = $certification->CertificateDate->format('Y-m-d');

        $this['certificateForm']->setDefaults($certification);
    }

    protected function createComponentCertificateForm(): Form
    {
        $SemesterId = $this->getUser()->getIdentity()->semesterId;
        $classes = $this->studyClass->getClassesBySemester($SemesterId)->fetchPairs('ClassId', 'Name');
        $students = $this->student->getActualStudents($SemesterId)->fetchPairs('UserId', 'UserName');
        $marks = $this->mark->getMarks()->fetchPairs('Id', 'Name');

        $form = new Form;

        $form->addSelect('UserId')
            ->setItems($students);

        $form->addSelect('ClassId')
            ->setItems($classes);

        $form->addSelect('Certificate')
            ->setItems($marks);

        $form->addText('CertificateDate');

        $form->addSubmit('send');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'certificateFormSucceeded'];

        return $form;
    }

    public function certificateFormSucceeded(Form $form, \stdClass $values): void
    {

        $values = Utils::convertEmptyToNull($form->values);

        $this->transaction->startTransaction();

        bdump($values);
        $this->student->certificateStudent($values);
        $this->flashMessage('Známka na vysvědčení úspěšně zadána.','success');

        $this->transaction->endTransaction();
        $this->redirect('Overview:show');
    }

}