<?php


namespace App\Presenters;

use App\Model;
use App\Utils\Utils;
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
        $this->template->suggestionParents = $this->suggestion->getSuggestionParents();
        $this->template->suggestions = $this->suggestion->getSuggestions()->fetchAll();
    }

    public function renderEdit($suggestionId)
    {
        $userId = $this->user->getId();
        $suggestion = $this->suggestion->getUserSuggestion($suggestionId, $userId)->fetch();

        if (!$suggestion)
        {
            $this->flashMessage('Příspěvek nenalezen.','danger');
            $this->redirect('Suggestion:show');
        }

        $this['suggestionForm']->setDefaults($suggestion);
        $this->template->suggestion = $suggestion;
    }

    public function renderDelete($suggestionId)
    {
        $userId = $this->user->getId();
        $suggestion = $this->suggestion->getUserSuggestion($suggestionId, $userId)->fetch();

        if (!$suggestion)
        {
            $this->flashMessage('Příspěvek nenalezen.','danger');
            $this->redirect('Suggestion:show');
        }
    }

    public function actionReact($suggestionParentId)
    {
        $this->template->suggestions = $this->suggestion->getSuggestionsByParent($suggestionParentId);

        $this->template->parentId = $suggestionParentId;
    }

    public function actionError()
    {
        $this->template->userId = hash('ripemd160', $this->getUser()->getId());
        $this->template->errors = $this->error->getErrors();
    }

    public function actionErrorAdmin()
    {
        $this->template->userId = hash('ripemd160', $this->getUser()->getId());
        $this->template->errors = $this->error->getErrors();
    }

    public function actionResolution($errorId)
    {
        $this->template->error = $this->error->getErrorById($errorId)->fetch();
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

    protected function createComponentErrorResolutionForm(): Form
    {
        $form = new Form;

        $form->addInteger('Id');

        $form->addCheckbox('State');

        $form->addTextArea('Reaction')
            ->setRequired();

        $form->addSubmit('send');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'errorResolutionFormSucceeded'];

        return $form;
    }

    public function errorResolutionFormSucceeded(Form $form): void
    {
        $values = $form->values;

        $this->transaction->startTransaction();
        $this->error->updateError($values);
        $this->transaction->endTransaction();

        $this->flashMessage('Vyjádření k chybě zaznamenáno.','success');
        $this->redirect('Suggestion:errorAdmin');
    }

    protected function createComponentSuggestionForm(): Form
    {
        $form = new Form;

        $form->addInteger('Id');

        $form->addInteger('ParentId');

        $form->addInteger('UserId');

        $form->addText('Subject')
            ->setRequired('Prosím vyplňte předmět příspěvku.')
            ->setMaxLength(60);

        $form->addTextArea('Text')
            ->setRequired('Prosím vyplňte obsah příspěvku.');

        $form->addSubmit('send');

        $form->addProtection();

        $form->onError[] = array($this, 'suggestionForm');
        $form->onSuccess[] = [$this, 'suggestionFormSucceeded'];

        return $form;
    }

    public function suggestionFormSucceeded(Form $form): void
    {
        $values = Utils::convertEmptyToNull($form->getValues());
        $values->Datetime = date("Y-m-d H:i:s");

        $this->transaction->startTransaction();
        if ($values->Id) {
            $this->suggestion->updateSuggestion($values);
            $message = 'Příspěvek byl úspěšně upraven.';
        } else {
            $this->suggestion->insertSuggestion($values);
            $message = 'Příspěvek byl úspěšně přidán.';
        }
        $this->transaction->endTransaction();

        $this->flashMessage($message,'success');
        $this->redirect('Suggestion:show');
    }

    private function createErrorFile($file)
    {
        $fileName = NULL;
        if ($file->error == 0) {
            $dateNamePart = date("Ymd");
            $generateNamePart = Utils::generateString(16);
            $fileType = substr($file->name, -5, 5);
            $fileName = $dateNamePart . '-' . $generateNamePart . $fileType;

            $file->move(Utils::getAbsolutePath() . DIRECTORY_SEPARATOR . 'printscreens' . DIRECTORY_SEPARATOR . $fileName);
        }

        return $fileName;
    }

}