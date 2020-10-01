<?php


namespace App\Presenters;

use App\Model;
use App\Utils\Utils;
use Nette\Application\UI\Form;

class AssessmentPresenter extends BasePresenter
{
    private $assessment;

    /** @var Model\StudyClass
     * @inject
     */
    public $studyClass;

    /** @var Model\Student
     * @inject
     */
    public $student;

    /** @var Model\Homework
     * @inject
     */
    public $homework;

    /** @var Model\Mark
     * @inject
     */
    public $mark;


    /** @var Model\Transaction
     * @inject
     */
    public $transaction;

    public function __construct(Model\Assessment $assessment)
    {
        $this->assessment = $assessment;
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
        $SemesterId = $this->getUser()->getIdentity()->semesterId;
        $this->template->assessments = $this->assessment->getAssessmentBySemester($SemesterId);
    }

    public function renderCreate()
    {
        $SemesterId = $this->getUser()->getIdentity()->semesterId;
        $this->template->assessments = $this->assessment->getAssessmentBySemester($SemesterId);
    }

    public function renderEdit($StudentId, $ClassId, $AssessmentId)
    {
        $SemesterId = $this->getUser()->getIdentity()->semesterId;
        $this->template->assessments = $this->assessment->getAssessmentBySemester($SemesterId);
    }

    public function actionDelete($StudentId, $ClassId, $AssessmentId)
    {
        $asessment = $this->assessment->getStudentAssessment($StudentId, $ClassId, $AssessmentId)->fetch();

        $this->transaction->startTransaction();
        $this->assessment->deleteAssessment($StudentId, $ClassId, $AssessmentId);
        $this->transaction->endTransaction();

        $this->flashMessage('Studentovi ' . $asessment->UserName . ' byla známka ' . $asessment->MarkName . ' za úkol ('.
            $asessment->HomeworkCode . ' - ' .$asessment->AssessmentName .') úspěšně smazána.','success');
        $this->redirect('Assessment:show');
    }

    protected function createComponentAssessmentForm(): Form
    {
        $SemesterId = $this->getUser()->getIdentity()->semesterId;
        $classes = $this->studyClass->getClassesBySemester($SemesterId)->fetchPairs('ClassId', 'Name');
        $students = $this->student->getActualStudents($SemesterId)->fetchPairs('UserId', 'UserName');
        $homeworks = $this->homework->getHomeworksBySemester($SemesterId)->fetchPairs('Id', 'HomeworkName');
        $marks = $this->mark->getMarks()->fetchPairs('Id', 'Name');

        $form = new Form;


        $form->addSelect('StudentClassId')
            ->setItems($classes);

        $form->addSelect('StudentUserId')
            ->setItems($students);

        $form->addSelect('AssessmentId')
            ->setItems($homeworks);

        $form->addSelect('MarkId')
            ->setItems($marks);

        $form->addText('Date');

        $form->addTextArea('Comment');

        $form->addSubmit('send');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'assessmentFormSucceeded'];

        return $form;
    }

    public function assessmentFormSucceeded(Form $form, \stdClass $values): void
    {
        $values = Utils::convertEmptyToNull($form->values);

        $this->transaction->startTransaction();
        $this->assessment->insertAssessment($values);
        $this->transaction->endTransaction();

        $this->flashMessage('Známka úspěšně přidána.','success');
        $this->redirect('Assessment:show');
    }

}