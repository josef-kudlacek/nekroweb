<?php


namespace App\Presenters;

use App\Model;
use App\utils\Utils;
use Nette\Application\UI\Form;

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
        if (!$this->getUser()->isInRole('Profesor')) {
            $this->flashMessage('Přístup do neoprávněné sekce. Proběhlo přesměrování na hlavní stránku.','danger');
            $this->redirect('Homepage:default');
        }
    }

    public function actionNew($ClassId, $LessonId)
    {
        $this->template->class = $this->studyClass->getClassById($ClassId)->fetch();
        $this->template->lesson = $this->lesson->getLessonById($LessonId)->fetch();
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

    private function createArcName()
    {
        $dateNamePart = date("Ymd");
        $generateNamePart = Utils::generateString(28);

        return $dateNamePart.$generateNamePart.'.pdf';
    }

}