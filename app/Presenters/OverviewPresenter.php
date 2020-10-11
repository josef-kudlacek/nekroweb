<?php


namespace App\Presenters;

use App\Model;

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

    /** @var Model\StudentAssessment
     * @inject
     */
    public $studentAssessment;

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

}