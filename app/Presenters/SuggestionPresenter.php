<?php


namespace App\Presenters;

use App\Model;

class SuggestionPresenter extends BasePresenter
{
    private $suggestion;

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

}