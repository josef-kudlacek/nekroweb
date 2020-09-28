<?php


namespace App\Presenters;

use App\Model;
use App\utils\Utils;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Application\Responses\FileResponse;
use Nette\Utils\FileSystem;


class ArcPresenter extends BasePresenter
{
    private $arc;

    /** @var Model\Transaction
     * @inject
     */
    public $transaction;

    /** @var Model\Lesson
     * @inject
     */
    public $lesson;

    /** @var Model\StudyClass
     * @inject
     */
    public $studyClass;

    public function __construct(Model\Arc $arc)
    {
        $this->arc = $arc;
    }

    protected function startup()
    {
        parent::startup();
        if (!$this->getUser()->loggedIn) {
            $this->flashMessage('Přístup do této sekce je pouze pro přihlášené. Přihlaste se prosím.','danger');
            $this->redirect('Sign:in');
        }
    }

    public function actionShow()
    {
        $classId = $this->getUser()->getIdentity()->classId;

        $this->template->class = $this->studyClass->getClassById($classId)->fetch();
        $this->template->arcs = $this->arc->getArcsByClass($classId);
    }

    public function actionNew($ClassId, $LessonId)
    {
        $this->checkAccess();

        $this->template->class = $this->studyClass->getClassById($ClassId)->fetch();
        $this->template->lesson = $this->lesson->getLessonById($LessonId)->fetch();
    }

    public function actionDelete($fileName)
    {
        $this->checkAccess();

        $filepath =  Utils::getAbsolutePath() . DIRECTORY_SEPARATOR . 'arch' . DIRECTORY_SEPARATOR . $fileName;
        FileSystem::delete($filepath);

        $this->transaction->startTransaction();
        $this->arc->deleteArc($fileName);
        $this->transaction->endTransaction();

        $this->flashMessage('Arch '. $fileName .' úspěšně smazán.','success');
        $this->redirect('Attendance:admin');
    }

    public function actionDownload($fileName)
    {
        $arcInfo = $this->arc->getArcName($fileName)->fetch();
        $downloadName = $this->createArcNameforDown($arcInfo);

        $filepath =  Utils::getAbsolutePath() . DIRECTORY_SEPARATOR . 'arch' . DIRECTORY_SEPARATOR . $fileName;
        $httpResponse = $this->context->getService("httpResponse");
        $httpResponse->setHeader("Pragma", "public");
        $httpResponse->setHeader("Expires", 0);
        $httpResponse->setHeader("Content-Description", "File Transfer");
        $httpResponse->setHeader("Content-Length", filesize($filepath));

        try {
            $response = new FileResponse($filepath, $downloadName, "application/pdf", true);
            bdump($response);
            $this->sendResponse($response);
        } catch (BadRequestException $e) {
            $this->flashMessage('Soubor ' . $fileName . ' neexistuje nebo není čitelný.','danger');
            $this->redirect('Homepage:default');
        }
    }

    protected function createComponentArcForm(): Form
    {
        $form = new Form;

        $form->addInteger('ClassId');

        $form->addInteger('LessonId');

        $form->addText('FileName');

        $form->addUpload('ArcFile', 'Nahrát arch hodiny')
            ->addRule(Form::MAX_FILE_SIZE, 'Soubor je příliš velký, limit je 3 MB!', 3 * 1024 * 1024)
            ->setRequired();

        $form->addSubmit('send', 'Nahrát arch');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'arcFormSucceeded'];

        return $form;
    }

    public function arcFormSucceeded(Form $form, \stdClass $values): void
    {
        $this->checkAccess();

        $values = Utils::convertEmptyToNull($form->getValues());
        $fileName = $this->createArcName();
        $values->FileName = $fileName;

        $this->transaction->startTransaction();
        $values->ArcFile->move(Utils::getAbsolutePath() . DIRECTORY_SEPARATOR . 'arch' . DIRECTORY_SEPARATOR . $fileName);
        $this->arc->insertArc($values);
        $this->transaction->endTransaction();

        $this->flashMessage('Arch: ' . $fileName . ' úspěšně nahrán.','success');
        $this->redirect('Attendance:admin');
    }

    private function checkAccess()
    {
        if (!$this->getUser()->isInRole('Profesor')) {
            $this->flashMessage('Přístup do neoprávněné sekce. Proběhlo přesměrování na hlavní stránku.','danger');
            $this->redirect('Homepage:default');
        }
    }

    private function createArcName()
    {
        $dateNamePart = date("Ymd");
        $generateNamePart = Utils::generateString(28);

        return $dateNamePart.$generateNamePart.'.pdf';
    }

    private function createArcNameforDown($arcInfo)
    {
        return date("Ymd", strtotime($arcInfo->AttendanceDate)) . '_' . $arcInfo->ClassName . '_' . $arcInfo->LessonNumber  . '_arch.pdf';
    }

}