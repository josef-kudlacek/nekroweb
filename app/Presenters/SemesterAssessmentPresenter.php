<?php


namespace App\Presenters;

use App\Model;
use App\Utils\Utils;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Form;
use Nette\Utils\FileSystem;

class SemesterAssessmentPresenter extends BasePresenter
{
    private $semesterAssessment;

    /** @var Model\StudentAssessment
     * @inject
     */
    public $studentAssessment;

    /** @var Model\Transaction
     * @inject
     */
    public $transaction;

    public function __construct(Model\SemesterAssessment $semesterAssessment)
    {
        $this->semesterAssessment = $semesterAssessment;
    }

    protected function startup()
    {
        parent::startup();
        if (!$this->getUser()->loggedIn) {
            $this->flashMessage('Přístup do této sekce je pouze pro přihlášené. Přihlaste se prosím.','danger');
            $this->redirect('Sign:in');
        }
    }

    public function renderShow()
    {
        $classId = $this->user->getIdentity()->classId;

        $this->template->assessments = $this->semesterAssessment->getAssessmentsInSemesterByClassId($classId)->fetchAll();
    }

    public function actionMy($assessmentId)
    {
        $userId = $this->user->getId();
        $classId = $this->user->getIdentity()->classId;

        $assessment = $this->semesterAssessment->getAssessmentsInSemesterByAssessmentAndClassId($assessmentId, $classId)->fetch();
        $studentAssessment = $this->studentAssessment->getStudentAssessmentsByStudentClassAndAssesmentId($userId, $classId, $assessmentId)->fetch();
        $this['studentAssessmentForm']->setDefaults([
            'StudentUserId' => $userId,
            'StudentClassId' => $classId,
            'AssessmentId' => $assessmentId
        ]);

        if (!$assessment) {
            $this->flashMessage('Takový úkol neexistuje.','danger');
            $this->redirect('SemesterAssessment:show');
        }

        $this->template->assessment = $assessment;
        $this->template->studentAssessment = $studentAssessment;
    }

    public function renderShared($assessmentId)
    {
        $classId = $this->user->getIdentity()->classId;

        $assessment = $this->semesterAssessment->getAssessmentsInSemesterByAssessmentAndClassId($assessmentId, $classId)->fetch();
        $assessments = $this->studentAssessment->getStudentAssessmentsByAssessmentIdAndClass($assessmentId, $classId)->fetchAll();

        if (!$assessment) {
            $this->flashMessage('Takový úkol neexistuje.','danger');
            $this->redirect('SemesterAssessment:show');
        }

        $this->template->assessmentId = hash('ripemd160', $assessmentId);
        $this->template->assessment = $assessment;
        $this->template->assessments = $assessments;
    }

    public function actionDownload($fileName)
    {
        $fileInfo = $this->studentAssessment->getStudentAssessmentsByFileName($fileName)->fetch();
        $downloadName = $this->createFileNameforDown($fileInfo);

        $filepath =  Utils::getAbsolutePath() . DIRECTORY_SEPARATOR . 'assessment' . DIRECTORY_SEPARATOR . $fileName;
        $httpResponse = $this->context->getService("httpResponse");
        $httpResponse->setHeader("Pragma", "public");
        $httpResponse->setHeader("Expires", 0);
        $httpResponse->setHeader("Content-Description", "File Transfer");
        $httpResponse->setHeader("Content-Length", filesize($filepath));

        try {
            $response = new FileResponse($filepath, $downloadName, null, true);
            $this->sendResponse($response);
        } catch (BadRequestException $e) {
            $this->flashMessage('Zpracování ' . $fileName . ' neexistuje nebo není čitelné.','danger');
            $this->redirect('SemesterAssessment:show');
        }
    }

    public function actionDelete($fileName)
    {
        $filepath =  Utils::getAbsolutePath() . DIRECTORY_SEPARATOR . 'assessment' . DIRECTORY_SEPARATOR . $fileName;
        FileSystem::delete($filepath);

        $this->transaction->startTransaction();
        $this->studentAssessment->deleteAssessmentFileName($fileName);
        $this->transaction->endTransaction();

        $this->flashMessage('Zpracování '. $fileName .' úspěšně smazáno.','success');
        $this->redirect('SemesterAssessment:show');
    }

    protected function createComponentStudentAssessmentForm(): Form
    {
        $form = new Form;

        $form->addInteger('StudentUserId');

        $form->addInteger('StudentClassId');

        $form->addInteger('AssessmentId');

        $form->addUpload('StudentAssessmentFile', 'Nahrát moje zpracování')
            ->addRule(Form::MAX_FILE_SIZE, 'Soubor je příliš velký, limit je 30 MB!', 30 * 1024 * 1024)
            ->addRule(Form::MIME_TYPE, 'Soubor není podporovaného formátu!',
                '.bmp, .doc, .docx, .gif, .jpg, .odp, .ods, .odt, .pdf, .png, .ppt, .pptx, .rtf, .txt, .xls, .xlsx')
            ->setRequired();

        $form->addSubmit('send');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'studentAssessmentFormSucceeded'];

        return $form;
    }

    public function studentAssessmentFormSucceeded(Form $form, \stdClass $values): void
    {
        $values = Utils::convertEmptyToNull($form->getValues());
        $fileName = $this->createFileName($values->StudentAssessmentFile);
        $values->fileName = $fileName;

        $this->transaction->startTransaction();
        $values->StudentAssessmentFile->move(Utils::getAbsolutePath() . DIRECTORY_SEPARATOR . 'assessment' . DIRECTORY_SEPARATOR . $fileName);
        unset($values['StudentAssessmentFile']);

        $this->studentAssessment->insertStudentAssessment($values);
        $this->transaction->endTransaction();

        $this->flashMessage('Zpracování: ' . $fileName . ' úspěšně nahráno.','success');
        $this->redirect('SemesterAssessment:show');
    }

    private function createFileName($StudentAssessmentFile)
    {
        $dateNamePart = date("Ymd");
        $generateNamePart = Utils::generateString(31);
        $fileExtension = explode('.' , $StudentAssessmentFile->name);

        return $dateNamePart.$generateNamePart.'.'.end($fileExtension);
    }

    private function createFileNameforDown($fileInfo)
    {
        $parts = explode('.' , $fileInfo->FileName);
        $YearTo = (!is_null($fileInfo->YearTo) ? '-' .$fileInfo->YearTo : '');

        return $fileInfo->YearFrom . $YearTo . '_' . $fileInfo->StudentName . '_' . $fileInfo->HomeworkCode . '_' . $fileInfo->AssessmentName . '.' . end($parts);
    }
}