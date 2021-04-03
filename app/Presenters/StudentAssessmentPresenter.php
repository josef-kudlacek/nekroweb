<?php


namespace App\Presenters;

use App\Model;

class StudentAssessmentPresenter extends BasePresenter
{
    private $studentAssessment;

    /** @var Model\SemesterAssessment
     * @inject
     */
    public $semesterAssessment;

    public function __construct(Model\StudentAssessment $studentAssessment)
    {
        $this->studentAssessment = $studentAssessment;
    }

    protected function startup()
    {
        parent::startup();
        if (!$this->getUser()->loggedIn) {
            $this->flashMessage('Přístup do této sekce je pouze pro přihlášené. Přihlaste se prosím.','danger');
            $this->redirect('Sign:in');
        }
    }

    public function renderClass()
    {
        $classId = $this->user->getIdentity()->classId;

        $this->template->classId = hash('ripemd160', $classId);
        $this->template->assessmentsHeader = $this->semesterAssessment->getAssessmentsInSemesterByClassId($classId)->fetchPairs('Code', 'Name');
        $this->template->assessments = $this->studentAssessment->getStudentAssessmentsByClass($classId)->fetchAll();
    }

    public function renderShow()
    {
        $userId = $this->user->getId();
        $classId = $this->user->getIdentity()->classId;

        $this->template->classId = hash('ripemd160', $classId);
        $this->template->assessment = $this->studentAssessment->getStudentAssessments($userId, $classId)->fetchAll();
    }

}