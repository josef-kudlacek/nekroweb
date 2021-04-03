<?php


namespace App\Presenters;

use App\Model;
use App\Utils\Utils;
use Nette\Application\UI\Form;

class StudyClassPresenter extends BasePresenter
{
    private $studyClass;

    /** @var Model\Transaction
     * @inject
     */
    public $transaction;

    /** @var Model\Semester
     * @inject
     */
    public $semester;

    /** @var Model\Year
     * @inject
     */
    public $year;

    public function __construct(Model\StudyClass $studyClass)
    {
        $this->studyClass = $studyClass;
    }

    protected function startup()
    {
        parent::startup();
        if (!$this->getUser()->loggedIn) {
            $this->flashMessage('Přístup do této sekce je pouze pro přihlášené. Přihlaste se prosím.','danger');
            $this->redirect('Sign:in');
        }
    }

    public function renderPoints()
    {
        $classId = $this->getUser()->getIdentity()->classId;

        $this->template->classId = hash('ripemd160', $classId);
        $this->template->students = $this->studyClass->getPointsSumByClass($classId);
    }

    public function renderShow()
    {
        $this->checkAccess();

        $semesterId = $this->getUser()->getIdentity()->semesterId;

        $this->template->semesterId = hash('ripemd160', $semesterId);
        $this->template->studyClass = $this->studyClass->getClassesBySemester($semesterId);
    }

    public function renderCreate()
    {
        $this->checkAccess();

        $this['studyClassForm']->setDefaults([
            'semester' => $this->getUser()->getIdentity()->semesterId
        ]);
    }

    public function actionEdit($ClassId)
    {
        $this->checkAccess();

        $studyClass = $this->studyClass->getClassById($ClassId)->fetch();
        if (!$studyClass) {
            $this->flashMessage('Třída nenalezena.', "danger");;
            $this->redirect('StudyClass:show');
        }

        $this['studyClassForm']->setDefaults([
            'name' => $studyClass->Name,
            'firstlesson' => $studyClass->FirstLesson,
            'lastlesson' => $studyClass->LastLesson,
            'timefrom' => $studyClass->TimeFrom->format('H:M:S'),
            'timeto' => $studyClass->TimeTo->format('H:M:S'),
            'semester' => $studyClass->SemesterId,
            'year' => $studyClass->YearId
        ]);
    }

    public function actionDelete($ClassId)
    {
        $this->checkAccess();

        $this->transaction->startTransaction();
        $this->studyClass->deleteClassById($ClassId);
        $this->transaction->endTransaction();

        $this->flashMessage('Třída úspěšně odebrána.', "success");
        $this->redirect('StudyClass:show');
    }

    protected function createComponentStudyClassForm(): Form
    {
        $form = new Form;

        $form->addText('name')
            ->setRequired('Prosím vyplňte název třídy.')
            ->setMaxLength(3);

        $form->addText('firstlesson');

        $form->addText('lastlesson');

        $form->addText('timefrom')
            ->setRequired('Vyplňte prosím čas třídy od.');

        $form->addText('timeto')
            ->setRequired('Vyplňte prosím čas třídy do.');

        $semesters = $this->semester->getSemesters();
        $selectItems = Utils::prepareSemesterSelectBoxArray($semesters);

        $form->addSelect('semester')
            ->setItems($selectItems)
            ->setRequired('Vyberte prosím semestr.');

        $years = $this->year->GetYears()->fetchPairs('Id', 'CodeName');

        $form->addSelect('year')
            ->setItems($years)
            ->setRequired('Vyberte ročník nekromancie.');

        $form->addSubmit('send');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'studyClassFormSucceeded'];

        return $form;
    }

    public function studyClassFormSucceeded(Form $form, \stdClass $values): void
    {
        $studyClassId = $this->getParameter('ClassId');
        $values = Utils::convertEmptyToNull($form->getValues());
        $this->transaction->startTransaction();

        if ($studyClassId) {
            $this->studyClass->updateClassById($values, $studyClassId);
            $this->flashMessage('Třída '.
                $values->name . ' byla úspěšně upravena.', 'success');
        } else {
            $this->studyClass->insertClass($values);
            $this->flashMessage('Třída '.
                $values->name . ' byla úspěšně vložena.', 'success');
        }

        $this->transaction->endTransaction();
        $this->redirect('StudyClass:show');
    }

    private function checkAccess()
    {
        if (!$this->getUser()->isInRole('Profesor')) {
            $this->flashMessage('Přístup do neoprávněné sekce. Proběhlo přesměrování na hlavní stránku.','danger');
            $this->redirect('Homepage:default');
        }
    }
}