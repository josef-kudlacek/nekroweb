<?php


namespace App\Presenters;

use App\Model;
use App\Utils\Utils;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Application\Responses\FileResponse;
use Nette\Utils\FileSystem;

class CompetitionPresenter extends BasePresenter
{
    private $competition;

    /** @var Model\Transaction
     * @inject
     */
    public $transaction;

    /** @var Model\StudyClass
     * @inject
     */
    public $studyClass;

    /** @var Model\CompetitionFile
     * @inject
     */
    public $competitionFile;

    public function __construct(Model\Competition $competition)
    {
        $this->competition = $competition;
    }

    protected function startup()
    {
        parent::startup();
        if (!$this->getUser()->loggedIn) {
            $this->flashMessage('Přístup do této sekce je pouze pro přihlášené. Přihlaste se prosím.','danger');
            $this->redirect('Sign:in');
        }
    }

    public function actionSemester()
    {
        $this->checkAccess();

        $semesterId = $this->getUser()->getIdentity()->semesterId;
        $this->template->competitions = $this->competition->getCompetitionBySemester($semesterId);
    }

    public function actionAdmin()
    {
        $this->checkAccess();

        $semesterId = $this->getUser()->getIdentity()->semesterId;
        $this->template->competitions = $this->competition->getCompetitionBySemester($semesterId);
    }

    public function actionShow()
    {
        $classId = $this->getUser()->getIdentity()->classId;
        $this->template->competitions = $this->competition->getCompetitionByClassId($classId);
    }

    public function actionCreate()
    {
        $this->checkAccess();
    }

    public function actionEdit($competitionId)
    {
        $this->checkAccess();

        $competition = $this->competition->getCompetitionById($competitionId)->fetch();

        $competition->CompetitionDate = $competition->CompetitionDate->format('Y-m-d');
        $this['competitionForm']->setDefaults($competition);
    }

    public function actionUpload($competitionId)
    {
        $this->checkAccess();

        $this->template->competition = $this->competition->getCompetitionById($competitionId)->fetch();
    }

    public function actionDetail($competitionId)
    {
        if ($this->user->isInRole('Profesor')) {
            $competition = $this->competition->getCompetitionById($competitionId)->fetch();
        } else {
            $classId = $this->getUser()->getIdentity()->classId;
            $competition = $this->competition->getCompetitionByIdAndClass($competitionId, $classId)->fetch();
        }

        if (!$competition) {
            $this->flashMessage('Takové zadání CS neexistuje.','danger');
            $this->redirect('Competition:show');
        }

        $this->template->competition = $competition;
    }

    protected function createComponentCompetitionForm(): Form
    {
        $semesterId = $this->getUser()->getIdentity()->semesterId;

        $classes = $this->studyClass->getClassesBySemester($semesterId)->fetchPairs('ClassId', 'Name');


        $form = new Form;

        $form->addText('Id');

        $form->addText('SemesterId')
            ->setDefaultValue($semesterId);

        $form->addSelect('ClassId')
            ->setItems($classes)
            ->setRequired();

        $form->addText('CompetitionNumber')
            ->setRequired();

        $form->addText('CompetitionName')
            ->setRequired();

        $form->addText('CompetitionDate')
            ->setRequired();

        $form->addSubmit('send');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'competitionFormSucceeded'];

        return $form;
    }

    public function competitionFormSucceeded(Form $form, \stdClass $values): void
    {

        $values = Utils::convertEmptyToNull($form->values);

        $this->transaction->startTransaction();

        if ($values->Id)
        {
            $this->competition->updateCompetition($values);
            $this->flashMessage('Zadání CS ' . $values->CompetitionNumber . ' – ' .
                $values->CompetitionName . ' úspěšně upraveno.','success');
        } else {
            $this->competition->insertCompetition($values);
            $this->flashMessage('Zadání CS ' . $values->CompetitionNumber . ' – ' .
                $values->CompetitionName . 'úspěšně vytvořeno.','success');
        }

        $this->transaction->endTransaction();

        $this->redirect('Competition:semester');
    }

    protected function createComponentCompetitionFilesForm(): Form
    {
        $form = new Form;

        $form->addText('CompetitionId');

        $form->addMultiUpload('CompetitionFiles', 'Soubory:')
            ->addRule($form::MAX_LENGTH, 'Maximálně lze nahrát %d souborů', 10);

        $form->addSubmit('send');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'competitionFilesFormSucceeded'];

        return $form;
    }

    public function competitionFilesFormSucceeded(Form $form, \stdClass $values): void
    {

        $values = Utils::convertEmptyToNull($form->values);

        $this->transaction->startTransaction();

        $values = $this->prepareCompetitionFiles($values);
        $this->competitionFile->insertCompetitionFile($values);
        $this->flashMessage('Upload úspěšně proveden.','success');
        $this->transaction->endTransaction();

        $this->redirect('Competition:admin');
    }

    private function checkAccess()
    {
        if (!$this->getUser()->isInRole('Profesor')) {
            $this->flashMessage('Přístup do neoprávněné sekce. Proběhlo přesměrování na hlavní stránku.','danger');
            $this->redirect('Homepage:default');
        }
    }

    private function prepareCompetitionFiles($values)
    {
        $competition = $this->competition->getCompetitionById($values->CompetitionId)->fetch();

        $fileNames = array();
        foreach ($values->CompetitionFiles as $key => $value)
        {
            $fileName = $this->createCompetitionFileName($competition, $key);
            $fileExtension = explode('.' , $value->name);
            $fileName = $fileName.'.'.end($fileExtension);

            array_push($fileNames, array("CompetitionId" => $competition->Id, "FileName" => $fileName));
            $value->move(Utils::getAbsolutePath() . DIRECTORY_SEPARATOR . 'cs' . DIRECTORY_SEPARATOR . $fileName);
        }

        return $fileNames;
    }

    private function createCompetitionFileName($competition, $index)
    {
        $dateNamePart = $competition->SemesterId.'_'.$competition->ClassName.'_'.$competition->CompetitionNumber.'_'.$index;
        $generateNamePart = Utils::generateString(15);

        return $dateNamePart.'_'.$generateNamePart;
    }
}