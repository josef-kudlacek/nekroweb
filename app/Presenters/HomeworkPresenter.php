<?php


namespace App\Presenters;

use App\Model;
use App\Utils\Utils;
use Nette\Application\UI\Form;

class HomeworkPresenter extends BasePresenter
{
    private $homework;

    /** @var Model\HomeworkType
     * @inject
     */
    public $homeworkType;

    /** @var Model\Year
     * @inject
     */
    public $year;

    /** @var Model\SemesterAssessment
     * @inject
     */
    public $semesterAssessment;

    /** @var Model\Assessment
     * @inject
     */
    public $assessment;

    /** @var Model\StudyClass
     * @inject
     */
    public $studyClass;

    /** @var Model\Transaction
     * @inject
     */
    public $transaction;


    public function __construct(Model\Homework $homework)
    {
        $this->homework = $homework;
    }

    protected function startup()
    {
        parent::startup();
        if (!$this->getUser()->isInRole('Profesor')) {
            $this->flashMessage('Přístup do neoprávněné sekce. Proběhlo přesměrování na hlavní stránku.', 'danger');
            $this->redirect('Homepage:default');
        }
    }

    public function actionShow()
    {
        $semesterId = $this->user->getIdentity()->semesterId;
        $this->template->assessments = $this->assessment->getAssessmentsBySemester($semesterId);
    }

    public function renderEdit($semesterAssessmentId)
    {
        $assessment = $this->assessment->getSemesterAssessmentById($semesterAssessmentId)->fetch();

        $this['homeworkForm']->setDefaults($assessment);
    }

    public function renderAdd($assessmentId, $classId)
    {

        $assessment = $this->assessment->getAssessmentById($assessmentId)->fetch();
        $assessment->ClassId = $classId;
        $assessment->AssessmentId = $assessment->Id;
        $assessment->Id = NULL;

        $this['homeworkForm']->setDefaults($assessment);
    }

    public function actionDelete($semesterAssessmentId)
    {

        $this->transaction->startTransaction();
        $this->semesterAssessment->removeAssessmentFromSemester($semesterAssessmentId);
        $this->transaction->endTransaction();

        $this->flashMessage('Úloha úspěšně odebrána do semestru.','success');
        $this->redirect('Homework:show');
    }

    protected function createComponentHomeworkForm(): Form
    {
        $SemesterId = $this->getUser()->getIdentity()->semesterId;

        $homeworkTypes = $this->homeworkType->getHomeworkTypes()->fetchPairs('Id', 'Name');
        $years = $this->year->getYearsBySemester($SemesterId)->fetchPairs('Id', 'CodeName');
        $classes = $this->studyClass->getClassesBySemester($SemesterId)->fetchPairs('ClassId', 'Name');


        $form = new Form;

        $form->addText('Id');

        $form->addText('AssessmentId');

        $form->addSelect('ClassId')
            ->setItems($classes)
            ->setRequired();

        $form->addText('Name')
            ->setRequired();

        $form->addText('Weight')
            ->setRequired();

        $form->addSelect('YearId')
            ->setItems($years)
            ->setRequired();

        $form->addSelect('HomeworkTypeId')
            ->setItems($homeworkTypes);

        $form->addTextArea('Task');

        $form->addText('Code');

        $form->addSubmit('send');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'homeworkFormSucceeded'];

        return $form;
    }

    public function homeworkFormSucceeded(Form $form, \stdClass $values): void
    {

        $values = Utils::convertEmptyToNull($form->values);

        $assessment = $this->prepareAssessment($values);
        $homework = $this->prepareHomework($values);
        $semesterAssessment = $this->prepareSemesterAssessment($values);

        $this->transaction->startTransaction();

        bdump($values);
        if ($values->Id)
        {
            $this->assessment->updateAssessment($assessment);
            $this->semesterAssessment->updateRecord((array) $semesterAssessment);
            $this->homework->updateHomework((array) $homework);

            $this->flashMessage('Úloha úspěšně upravena.','success');
        } else {
            if (!$values->AssessmentId)
            {
                $this->assessment->insertAssessment($assessment);
            }

            $this->semesterAssessment->createRecord($semesterAssessment);
            $this->homework->createRecord($homework);
            $this->flashMessage('Úloha úspěšně vytvořena a přidána do semestru.','success');
        }

        $this->transaction->endTransaction();

        $this->redirect('Homework:show');
    }

    private function prepareAssessment($values)
    {
        return array (
            "Id" => $values->AssessmentId,
            "Name" => $values->Name,
            "Weight" => $values->Weight,
            "YearId" => $values->YearId,
        );
    }

    private function prepareHomework($values)
    {
        $homework = new \stdClass;

        $homework->AssessmentId = $values->AssessmentId;
        $homework->HomeworkTypeId = $values->HomeworkTypeId;

        return $homework;
    }

    private function prepareSemesterAssessment($values)
    {
        $SemesterAssessment = new \stdClass;

        $SemesterId = $this->getUser()->getIdentity()->semesterId;

        $SemesterAssessment->Id = $values->Id;
        $SemesterAssessment->AssessmentId = $values->AssessmentId;
        $SemesterAssessment->SemesterId = $SemesterId;
        $SemesterAssessment->ClassId = $values->ClassId;
        $SemesterAssessment->Code = $values->Code;
        $SemesterAssessment->Task = $values->Task;

        return $SemesterAssessment;
    }
}