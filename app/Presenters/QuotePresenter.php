<?php


namespace App\Presenters;

use App\Model;
use App\Utils\Utils;
use Nette\Application\UI\Form;

class QuotePresenter extends BasePresenter
{
    /** @var Model\Quote
     * @inject
     */
    public $quote;

    /** @var Model\Transaction
     * @inject
     */
    public $transaction;

    protected function startup()
    {
        parent::startup();
        if (!$this->getUser()->loggedIn) {
            $this->flashMessage('Přístup do této sekce je pouze pro přihlášené. Přihlaste se prosím.','danger');
            $this->redirect('Sign:in');
        }
    }

    public function actionAdmin()
    {
        if (!$this->getUser()->isInRole('Profesor')) {
            $this->flashMessage('Přístup do neoprávněné sekce. Proběhlo přesměrování na hlavní stránku.','danger');
            $this->redirect('Homepage:default');
        }

        $this->template->quotes = $this->quote->getQuotes();
    }

    public function actionShow()
    {
        $studentId = $this->getUser()->getId();

        $this->template->quotes = $this->quote->getQuotesByStudent($studentId);
    }

    public function actionEdit($quoteId)
    {
        $quote = $this->quote->getQuoteById($quoteId)->fetch();

        if (!$this->getUser()->isInRole('Profesor')) {
            $this->checkAccess($quoteId);
        }

        $this['quoteForm']->setDefaults([
            'Id' => $quote->Id,
            'UserId' => $quote->UserId,
            'ClassId' => $quote->ClassId,
            'Source' => $quote->Source,
            'Text' => $quote->Text,
        ]);
    }

    public function actionDelete($quoteId)
    {
        if (!$this->getUser()->isInRole('Profesor')) {
            $this->checkAccess($quoteId);
        }

        $this->transaction->startTransaction();
        $this->quote->deleteQuote($quoteId);
        $this->transaction->endTransaction();

        $this->flashMessage('Hláška úspěšně smazána.','success');
        $this->redirect('Quote:show');
    }

    protected function createComponentQuoteForm(): Form
    {
        $studentId = $this->user->getId();
        $classId = $this->user->getIdentity()->classId;

        $form = new Form;

        $form->addText('Id');

        $form->addText('UserId')
            ->setDefaultValue($studentId);

        $form->addText('ClassId')
            ->setDefaultValue($classId);

        $form->addTextArea('Text')
            ->setRequired()
            ->setMaxLength(255);

        $form->addTextArea('Source')
            ->setRequired()
            ->setMaxLength(60);

        $form->addSubmit('send');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'quoteFormSucceeded'];

        return $form;
    }

    public function quoteFormSucceeded(Form $form, \stdClass $values): void
    {
        $values = Utils::convertEmptyToNull($form->values);
        $this->transaction->startTransaction();
        if ($values->Id)
        {
            $this->quote->updateQuote($values);

            $this->flashMessage('Hláška úspěšně upravena.','success');
        } else {
            $this->quote->insertQuote($values);
            $this->flashMessage('Hláška úspěšně přidána.','success');
        }

        $this->transaction->endTransaction();
        $this->redirect('Quote:show');
    }

    private function checkAccess($quoteId)
    {
        $studentId = $this->getUser()->getId();
        $quote = $this->quote->getQuoteByIdAndStudent($quoteId, $studentId)->fetch();

        if (!$quote) {
            $this->flashMessage('Taková hláška neexistuje.','danger');
            $this->redirect('Quote:show');
        }
    }

}