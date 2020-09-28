<?php


namespace App\Presenters;

use App\Model;
use App\utils\Utils;
use Nette\Application\UI\Form;

class SuggestionPresenter extends BasePresenter
{
    private $suggestion;

    /** @var Model\Error
     * @inject
     */
    public $error;

    /** @var Model\Transaction
     * @inject
     */
    public $transaction;


    public function __construct(Model\Suggestion $suggestion)
    {
        $this->suggestion = $suggestion;
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
        $this->template->suggestion = $this->suggestion->GetSuggestions();
        $this->template->suggestionComments = $this->suggestion->GetSuggestionComments();
    }

    public function actionError()
    {
        $this->template->errors = $this->error->getErrors();
    }

    protected function createComponentAddErrorForm(): Form
    {
        $form = new Form;

        $form->addTextArea('Description')
            ->setRequired();

        $form->addUpload('ErrorFile', 'Nahrát arch hodiny')
            ->addRule(Form::MAX_FILE_SIZE, 'Soubor je příliš velký, limit je 2 MB!', 2 * 1024 * 1024);

        $form->addSubmit('send');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'addErrorFormSucceeded'];

        return $form;
    }

    public function addErrorFormSucceeded(Form $form, \stdClass $values): void
    {
        $values->UserId = $this->user->getId();
        $values->Date = date("Ymd");

        $fileName = $this->createErrorFile($values->ErrorFile);
        $values->FileName = $fileName;
        unset ($values->ErrorFile);
        $valuesArray = (array) $values;

        $this->transaction->startTransaction();
        $this->error->insertError($valuesArray);
        $this->transaction->endTransaction();

        $this->flashMessage('Chyba úspěšně nahlášena.','success');
        $this->redirect('Suggestion:error');
    }

    private function createErrorFile($file)
    {
        $fileName = NULL;
        if ($file->error == 0) {
            $dateNamePart = date("Ymd");
            $generateNamePart = Utils::generateString(16);
            $fileType = substr($file->name, -5, 5);
            $fileName = $dateNamePart . '-' . $generateNamePart . $fileType;

            $file->move(Utils::getAbsolutePath() . DIRECTORY_SEPARATOR . 'error' . DIRECTORY_SEPARATOR . $fileName);
        }

        return $fileName;
    }

}